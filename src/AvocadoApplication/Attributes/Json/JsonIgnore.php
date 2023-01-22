<?php

namespace Avocado\AvocadoApplication\Attributes\Json;

use Attribute;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\ReflectionUtils;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonIgnore {

    public function __construct(
        private ?string $targetProperty = null,
        private ?string $targetClass = null,
        private ?ReflectionProperty $reflectionProperty = null
    ) {}

    public function getReflectionProperty(): ?ReflectionProperty {
        return $this->reflectionProperty;
    }

    public function getTargetClass(): ?string {
        return $this->targetClass;
    }

    public function getTargetProperty(): ?string {
        return $this->targetProperty;
    }

    public static function isAnnotated(string $className, string $propertyName): bool {
        try {
            $ref = new ReflectionProperty($className, $propertyName);

            return AnnotationUtils::isAnnotated($ref, self::class);
        } catch (\Exception) {
            return false;
        }
    }
}