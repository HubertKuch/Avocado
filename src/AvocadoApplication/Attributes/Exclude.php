<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;
use ReflectionClass;

#[Attribute]
class Exclude {

    public function __construct(
        private readonly array $classes,
        private readonly ?ReflectionClass $ref
    ) {}

    public function getClasses(): array {
        return $this->classes ?? [];
    }

    public function getRef(): ReflectionClass {
        return $this->ref;
    }
}
