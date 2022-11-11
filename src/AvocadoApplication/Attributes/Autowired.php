<?php

namespace AvocadoApplication\Attributes;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Autowired {

    public function __construct(
        private readonly ?string $autowiredResourceName = "",
        private readonly ?ReflectionProperty $reflectionProperty = null
    ) {}

    public function getAutowiredResourceName(): string {
        return $this->autowiredResourceName;
    }

    public function getReflectionProperty(): ?ReflectionProperty {
        return $this->reflectionProperty;
    }
}
