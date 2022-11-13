<?php

namespace Avocado\Tests\Unit\Application;

use Attribute;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestParam {

    public function __construct(private readonly string $name, private readonly mixed $defaultValue = null, private readonly bool $required = false) {}

    public static function isAnnotated(ReflectionParameter $reflectionProperty) {
        $requestParamAnnotation = $reflectionProperty->getAttributes(self::class);

        return !empty($requestParamAnnotation);
    }

    public static function getInstance(ReflectionParameter $reflectionProperty): RequestParam {
        $requestParamAnnotation = $reflectionProperty->getAttributes(self::class);

        return $requestParamAnnotation[0]->newInstance();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDefaultValue(): mixed {
        return $this->defaultValue;
    }

    public function isRequired(): bool {
        return $this->required;
    }
}