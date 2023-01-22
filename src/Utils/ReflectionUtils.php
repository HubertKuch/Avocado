<?php

namespace Avocado\Utils;

use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use AvocadoApplication\Attributes\Nullable;
use mysql_xdevapi\SqlStatement;
use PHPUnit\Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionAttribute;
use ReflectionException;
use Avocado\ORM\Attributes\Field;
use stdClass;
use Utils\Strings;

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

    /**
     * @throws ReflectionException
     * @throws MissingKeyException
     */
    public static function instanceFromArray(array $data, string $target): object {
        $ref = new ReflectionClass($target);
        $instance = $ref->newInstanceWithoutConstructor();
        $objectRef = new ReflectionObject($instance);

        foreach ($objectRef->getProperties() as $propertyRef) {
            $propertyRef->setAccessible(true);
            $name = $propertyRef->name;

            if (!array_key_exists($name, $data)) {
                $name = Strings::camelToUnderscore($name);
            }

            if (!array_key_exists($name, $data)) {
                if (
                    AnnotationUtils::isAnnotated($propertyRef, Nullable::class) &&
                    $propertyRef->getType()->allowsNull()
                ) {

                    if ($propertyRef->isInitialized($instance)) {
                        continue;
                    }

                    $propertyRef->setValue($instance, null);

                    continue;
                } else {
                    throw new MissingKeyException("Missing `{$propertyRef->getName()}` key in `{$propertyRef->getDeclaringClass()->getName()}` class");
                }
            }

            $isObjectProperty = ClassFinder::getClassReflectionByName($propertyRef->getType()->getName()) !== null;

            if ($isObjectProperty) {
                $propertyRef->setValue($instance, self::instanceFromArray($data[$name], $propertyRef->getType()->getName()));
            } else {
                $propertyRef->setValue($instance, $data[$name]);
            }
        }

        return $instance;
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

    /** @return string[] */
    public static function getAllTypes(object $object) {
        $ref = new ReflectionClass($object);
        $types = [$ref->getName()];

        $parentClass = $ref->getParentClass();
        $interfaces = $ref->getInterfaceNames();

        if ($parentClass) array_push($types, $parentClass->getName());

        return array_merge($types, $interfaces);
    }

    public static function implements(string $className, string $interface): bool {
        try {
            $ref = new ReflectionClass($className);

            return $ref->implementsInterface($interface);
        } catch (ReflectionException $e) {
            return false;
        }
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return ?T
     * */
    public static function instance(string $className, array $args): ?object {
        try {
            $ref = new ReflectionClass($className);

            return $ref->newInstanceArgs($args);
        } catch (ReflectionException $e) {
            return null;
        }
    }
}
