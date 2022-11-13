<?php

namespace Avocado\Tests\Unit\Application;

use Attribute;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestParam {

    public function __construct(public readonly string $name, public readonly mixed $defaultValue = null, public readonly bool $required = false) {}

    public static function isAnnotated(ReflectionParameter $reflectionProperty) {
        $requestParamAnnotation = $reflectionProperty->getAttributes(self::class);

        return !empty($requestParamAnnotation);
    }

    public static function getInstance(ReflectionParameter $reflectionProperty): RequestParam {
        $requestParamAnnotation = $reflectionProperty->getAttributes(self::class);

        return $requestParamAnnotation[0]->newInstance();
    }
}