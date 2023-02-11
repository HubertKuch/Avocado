<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;
use ReflectionClass;

#[Attribute]
class Avocado {

    public function __construct(private readonly ?string $className = "", private readonly ?ReflectionClass $reflectionClass = null) {}

    public function getClassName(): ?string {
        return $this->className ?? null;
    }

    public function getReflectionClass(): ?ReflectionClass {
        return $this->reflectionClass;
    }


}
