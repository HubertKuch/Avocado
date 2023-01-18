<?php

namespace AvocadoApplication\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Nullable {

    public function __construct(private readonly ?ReflectionProperty $targetReflection = null) {}

    public function getTargetReflection(): ?ReflectionProperty {
        return $this->targetReflection;
    }
}