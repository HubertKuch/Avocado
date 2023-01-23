<?php

namespace Avocado\AvocadoApplication\Interceptors\Utils;

use Avocado\Utils\AnnotationUtils;
use ReflectionMethod;

class WebRequestHandler {

    public function __construct(
        private ReflectionMethod $handlerRef
    ) {}

    public function hasAnnotation(string $targetAnnotation): bool {
        return AnnotationUtils::isAnnotated($this->handlerRef, $targetAnnotation);
    }

    public function getHandlerRef(): ReflectionMethod {
        return $this->handlerRef;
    }
}
