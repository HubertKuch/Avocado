<?php

namespace Avocado\AvocadoApplication\Attributes;

use Attribute;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestBody {

    public static function isAnnotated(ReflectionParameter $reflectionProperty) {
        $requestBodyAnnotations = $reflectionProperty->getAttributes(RequestBody::class);

        return !empty($requestBodyAnnotations);
    }

}