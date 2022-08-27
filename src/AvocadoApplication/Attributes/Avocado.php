<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;

#[Attribute]
class Avocado {

    public function __construct(
        private readonly string $className
    ) {}

    public function getClassName(): string {
        return $this->className;
    }
}
