<?php

namespace Dtomatic\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Converter
{
    public string $converterClass;

    public function __construct(string $converterClass)
    {
        $this->converterClass = $converterClass;
    }
}
