<?php

namespace Avocado\Tests\Unit\Application;

use Attribute;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestQuery {

    public function __construct(private readonly string $name, private readonly mixed $defaultValue = null, private readonly bool $required = false) {
    }

    public static function isAnnotated(ReflectionParameter $reflectionProperty) {
        $queryAnnotation = $reflectionProperty->getAttributes(self::class);

        return !empty($queryAnnotation);
    }

    public static function getInstance(ReflectionParameter $reflectionProperty): RequestQuery {
        $queryAnnotation = $reflectionProperty->getAttributes(self::class);

        return $queryAnnotation[0]->newInstance();
    }

    public function getDefaultValue(): mixed {
        return $this->defaultValue;
    }

    public function getName(): string {
        return $this->name;
    }

    public function isRequired(): bool {
        return $this->required;
    }
}