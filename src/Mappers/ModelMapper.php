<?php

namespace Dtomatic\Mappers;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Dtomatic\Attributes\ArrayOf;
use Dtomatic\Attributes\Ignore;
use Dtomatic\Attributes\Converter;

class ModelMapper
{
    /**
     * Map a source object to a destination DTO class.
     *
     * @throws \ReflectionException
     */
    public function map(object $source, string $destinationClass): object
    {
        $destination     = new $destinationClass();
        $sourceProps     = $this->extractSourceProps($source);
        $destReflection  = new ReflectionClass($destinationClass);
        $destProperties  = $destReflection->getProperties();
        $globalIgnores   = config('dtomatic.global_ignored_properties', []);
        $dateFormat      = config('dtomatic.date_format', 'Y-m-d H:i:s');
        $strictTypes     = config('dtomatic.strict_types', false);
        $globalConverters = config('dtomatic.custom_converters', []);

        foreach ($destProperties as $prop) {
            $propName = $prop->getName();

            if (in_array($propName, $globalIgnores)) {
                continue;
            }

            if ($prop->getAttributes(Ignore::class)) {
                continue;
            }

            // Check for getter first
            $getterMethod = 'get' . ucfirst($propName);
            if (method_exists($source, $getterMethod)) {
                $value = $source->$getterMethod();
            } elseif (array_key_exists($propName, $sourceProps)) {
                $value = $sourceProps[$propName];
            } else {
                continue;
            }

            $type = $prop->getType();

            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();

                // Property-level converter
                $converterAttrs = $prop->getAttributes(Converter::class);
                if (!empty($converterAttrs)) {
                    $converterClass = $converterAttrs[0]->newInstance()->converterClass;
                    $converter = app($converterClass);
                    if (method_exists($converter, 'convert')) {
                        $value = $converter->convert($value);
                    }
                } else {
                    // Global converter
                    $value = $this->applyCustomConverter($value, $globalConverters);
                }

                if (!$type->isBuiltin()) {
                    if (is_object($value)) {
                        $destination->$propName = $this->map($value, $typeName);
                    } elseif (is_array($value) || $value instanceof Collection) {
                        $arrayItemType = $this->getArrayItemType($prop);
                        $destination->$propName = $arrayItemType
                            ? $this->mapArrayItems($value, $arrayItemType)
                            : $this->map((object)$value, $typeName);
                    }
                } elseif ($typeName === 'array') {
                    $arrayItemType = $this->getArrayItemType($prop);
                    $destination->$propName = $arrayItemType
                        ? $this->mapArrayItems($value, $arrayItemType)
                        : $this->convertToArray($value);
                } elseif ($typeName === 'string' && $value instanceof \DateTimeInterface) {
                    $destination->$propName = $value->format($dateFormat);
                } else {
                    if ($strictTypes) {
                        $this->validateType($value, $typeName, $propName, $destinationClass);
                    }
                    $destination->$propName = $value;
                }
            } else {
                $destination->$propName = $value;
            }
        }

        return $destination;
    }

    /**
     * Map a Collection of source models to DTO array.
     */
    public function mapCollection(Collection $collection, string $dtoClass): array
    {
        return $collection->map(fn ($item) => $this->map($item, $dtoClass))->all();
    }

    /**
     * Extract property values from a model or plain object.
     */
    private function extractSourceProps(object $source): array
    {
        if ($source instanceof \Illuminate\Database\Eloquent\Model) {
            $props = [];

            foreach ($source->getAttributes() as $key => $value) {
                $props[$key] = $source->$key;
            }

            foreach ($source->getRelations() as $key => $relation) {
                $props[$key] = $relation;
            }

            return $props;
        }

        return method_exists($source, 'toArray')
            ? $source->toArray()
            : get_object_vars($source);
    }

    /**
     * Resolve ArrayOf attribute type.
     */
    private function getArrayItemType(ReflectionProperty $property): ?string
    {
        $attributes = $property->getAttributes(ArrayOf::class);
        return $attributes ? $attributes[0]->newInstance()->type : null;
    }

    /**
     * Map an array or Collection of items to DTO array.
     */
    private function mapArrayItems($items, string $itemType): array
    {
        if ($items instanceof Collection) {
            return $items->map(fn ($item) => $this->map($item, $itemType))->all();
        }

        if (is_array($items)) {
            return array_map(fn ($item) => $this->map((object)$item, $itemType), $items);
        }

        return [];
    }

    /**
     * Convert value to plain array.
     */
    private function convertToArray($value): array
    {
        return $value instanceof Collection
            ? $value->all()
            : (is_array($value) ? $value : []);
    }

    /**
     * Apply a custom converter from global config.
     */
    private function applyCustomConverter($value, array $converters)
    {
        foreach ($converters as $type => $converterClass) {
            if ($value instanceof $type) {
                $converter = app($converterClass);
                if (method_exists($converter, 'convert')) {
                    return $converter->convert($value);
                }
            }
        }

        return $value;
    }

    /**
     * Validate value type if strict_types is enabled.
     */
    private function validateType($value, string $expectedType, string $propName, string $dtoClass): void
    {
        $actualType = get_debug_type($value);

        $compatible = match ($expectedType) {
            'int'     => is_int($value),
            'float'   => is_float($value),
            'string'  => is_string($value),
            'bool'    => is_bool($value),
            'array'   => is_array($value),
            default   => $value instanceof $expectedType,
        };

        if (!$compatible) {
            throw new \InvalidArgumentException(
                "Type mismatch in '{$dtoClass}' for property '{$propName}': expected '{$expectedType}', got '{$actualType}'.",
                422
            );
        }
    }
}
