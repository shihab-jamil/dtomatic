<?php

namespace Dtomatic\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * @method static object map(object $source, string $destinationClass)
 * @method static array mapCollection(\Illuminate\Support\Collection $collection, string $dtoClass)
 */
class ModelMapper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Dtomatic\Mappers\ModelMapper::class;
    }
}
