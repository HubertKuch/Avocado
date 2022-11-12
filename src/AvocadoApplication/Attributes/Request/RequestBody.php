<?php

namespace Avocado\AvocadoApplication\Attributes\Request;

use Attribute;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestBody {

    public static function isAnnotated(ReflectionParameter $reflectionProperty) {
        $requestBodyAnnotations = $reflectionProperty->getAttributes(self::class);

        return !empty($requestBodyAnnotations);
    }

}