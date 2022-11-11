<?php

namespace Avocado\Utils;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionAttribute;
use ReflectionException;
use Avocado\ORM\Attributes\Field;

class ReflectionUtils {

    public static function modelFieldsToArray(object $object): array {
        $ref = new ReflectionClass($object);

        $fields = [];

        foreach ($ref->getProperties() as $property) {
            $propertyName = $property->getName();

            if (!empty($property->getAttributes(Field::class))) {
                if(!empty($property->getAttributes(Field::class)[0]->getArguments())) {
                    $propertyName = $property->getAttributes(Field::class)[0]->getArguments()[0];
                }
            }

            $fields += [$propertyName => $property->getValue($object)];
        }

        return $fields;
    }

    public static function getAttribute(object $object, string $attribute): ReflectionAttribute|null {
        $reflection = new ReflectionObject($object);

        $attribute = $reflection->getAttributes($attribute);

        return !empty($attribute)  ? $attribute[key($attribute)] : null;
    }

    public static function getAttributeFromClass(string|ReflectionClass $class, string $attribute): ReflectionAttribute|null {
        try {
            if (is_string($class)) {
                $reflection = new ReflectionClass($class);
            } else {
                $reflection = $class;
            }
        } catch (ReflectionException $e) {
            return null;
        }

        $attribute = $reflection->getAttributes($attribute);

        return !empty($attribute)  ? $attribute[key($attribute)] : null;
    }

    public static function getAttributeFromMethod(string $className, string $methodName, string $attribute): ReflectionAttribute|null {
        try {
            $reflection = new ReflectionMethod($className, $methodName);
        } catch (ReflectionException) {
            return null;
        }

        $attribute = $reflection->getAttributes($attribute);

        return !empty($attribute)  ? $attribute[key($attribute)] : null;
    }

    /** @return ReflectionMethod[] */
    public static function getMethods(string $className, ?string $attributeName = null): array {
        try {
            $methods = (new ReflectionClass($className))->getMethods();

            if (!is_null($attributeName)) {
                $methods = array_filter($methods, function($method) use ($attributeName) {
                    return !empty($method->getAttributes($attributeName));
                });
            }

            return $methods;
        } catch (ReflectionException) {
            return [];
        }
    }
}
