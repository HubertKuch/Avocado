<?php

namespace Avocado\Utils;

use ReflectionClass;
use ReflectionEnum;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;

class AnnotationUtils {

    public static function isAnnotated(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionEnum|ReflectionObject|ReflectionFunction|ReflectionParameter $what, string $annotationClass): bool {
        $annotation = $what->getAttributes($annotationClass);

        return !empty($annotation);

    }

    /**
     * @template T of object
     * @phpstan-param class-string<T> $annotation
     * @return object
     * @phpstan-return T
     */
    public static function getInstance(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionEnum|ReflectionObject|ReflectionFunction|ReflectionParameter $on, string $class): object {
        $ann = $on->getAttributes($class);

        return $ann[0]->newInstance();
    }

    public static function getInstances(ReflectionClass|ReflectionProperty|ReflectionMethod|ReflectionEnum|ReflectionObject|ReflectionFunction|ReflectionParameter $on, string $annotation): array {
        $annotations = $on->getAttributes($annotation);

        return array_map(fn($ann) => $ann->newInstance(), $annotations);
    }

}