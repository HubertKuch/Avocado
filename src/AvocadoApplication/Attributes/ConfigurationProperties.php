<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class ConfigurationProperties {

    private ?ReflectionClass $targetClass;
    private string $propertyPrefix;

    public function __construct(string $prefix) {
        $this->propertyPrefix = $prefix;
    }

    public function getPropertyPrefix(): string {
        return $this->propertyPrefix;
    }

    public function getTargetClass(): ReflectionClass {
        return $this->targetClass;
    }

    public function setTargetClass(?ReflectionClass $targetClass): void {
        $this->targetClass = $targetClass;
    }
}