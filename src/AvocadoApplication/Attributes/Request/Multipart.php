<?php

namespace Avocado\AvocadoApplication\Attributes\Request;

use Attribute;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Multipart {

    public function __construct(private readonly ?string $type = null) {}

    public static function isAnnotated(ReflectionParameter $reflectionProperty): bool {
        $requestBodyAnnotations = $reflectionProperty->getAttributes(self::class);

        return !empty($requestBodyAnnotations);
    }

    public function getType(): ?string {
        return $this->type;
    }

}