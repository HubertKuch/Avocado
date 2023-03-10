<?php

namespace Avocado\AvocadoApplication\Filters;

use Attribute;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class Filter {

    public function __construct(private ?string $target = null, private ?ReflectionClass $reflection = null) {}

    public function getTarget(): ?string {
        return $this->target;
    }

    public function getReflection(): ?ReflectionClass {
        return $this->reflection;
    }
}