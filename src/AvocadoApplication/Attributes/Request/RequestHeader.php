<?php

namespace Avocado\AvocadoApplication\Attributes\Request;

use Attribute;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestHeader {

    public function __construct(public readonly string $name){}

    public static function isAnnotated(ReflectionParameter $reflectionProperty): bool {
        $requestHeaderAnnotation = $reflectionProperty->getAttributes(self::class);

        return !empty($requestHeaderAnnotation);
    }

    public function getName(): string {
        return $this->name;
    }

    public static function getInstance(ReflectionParameter $reflectionProperty): RequestHeader {
        $requestHeaderAnnotation = $reflectionProperty->getAttributes(self::class);

        return $requestHeaderAnnotation[0]->newInstance();
    }
}