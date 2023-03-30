<?php

namespace Avocado\Utils;

use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\AvocadoORM\Attributes\Relations\JoinColumn;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use AvocadoApplication\Attributes\Nullable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Utils\Strings;
use function PHPUnit\Framework\containsEqual;

class ReflectionUtils {

    public static function modelFieldsToArray(object $object, bool $relationsToValue = false): array {
        $ref = new ReflectionClass($object);

        $fields = [];

        foreach ($ref->getProperties() as $property) {
            $propertyName = $property->getName();

            $field = AnnotationUtils::getInstance($property, Field::class);
            $id = AnnotationUtils::getInstance($property, Id::class);
            $join = AnnotationUtils::getInstance($property, JoinColumn::class);

            if ($field) {
                $name = empty($field->getField()) ? $propertyName : $field->getField();
                $value = $property->getValue($object);

                if (is_object($value) && enum_exists(get_class($value))) {
                    $value = $value->value;
                }

                $fields += [$name => $value];
            } else if ($id) {
                $name = empty($id->getField()) ? $propertyName : $id->getField();

                $fields += [$name => $property->getValue($object)];
            }

            if ($join) {
                $name = $join->getName();
                $value = $property->getValue($object);

                if (is_array($value) && empty($value)) {
                    $fields += [$name => $value];
                    continue;
                } else if (is_array($value)) {
                    $class = get_class($value[0]);
                } else {
                    $class = get_class($value);
                }

                if (class_exists($class) && !enum_exists($class)) {
                    if (!is_array($value)) {
                        $value = self::modelFieldsToArray($value, true);

                        if ($relationsToValue) {
                            $value = $value[$join->getReferencesTo()];
                        }
                    } else {
                        $value = array_map(function($entity) use ($relationsToValue, $join) {
                            $value = self::modelFieldsToArray($entity);

                            if ($relationsToValue) {
                                $value = $value[$join->getReferencesTo()];
                            }

                            return $value;
                        }, $value);
                    }
                }



                $fields += [$name => $value];
            }
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
            $name = $propertyRef->name;

            if (!array_key_exists($name, $data)) {
                $name = Strings::camelToUnderscore($name);
            }

            if (!array_key_exists($name, $data)) {
                if (AnnotationUtils::isAnnotated($propertyRef, Nullable::class) && $propertyRef->getType()
                                                                                               ->allowsNull()) {

                    if ($propertyRef->isInitialized($instance)) {
                        continue;
                    }

                    $propertyRef->setValue($instance, null);

                    continue;
                } else if ($propertyRef->getDefaultValue()) {
                    continue;
                } else {
                    throw new MissingKeyException("Missing `{$propertyRef->getName()}` key in `{$propertyRef->getDeclaringClass()->getName()}` class");
                }
            }


            $objectPropertyReflection = ClassFinder::getClassReflectionByName($propertyRef->getType()->getName());

            if ($objectPropertyReflection !== null && $objectPropertyReflection->isEnum()) {
                $enumRef = new ReflectionEnum($propertyRef->getType()->getName());

                $propertyRef->setValue($instance, $enumRef->getCase($data[$name]["name"] ?? $data[$name])->getValue());
            } else if ($objectPropertyReflection !== null) {
                $propertyRef->setValue($instance,
                    self::instanceFromArray($data[$name], $propertyRef->getType()->getName()));
            } else {
                $propertyRef->setValue($instance, $data[$name]);
            }
        }

        return $instance;
    }

    public static function getAttribute(object $object, string $attribute): ReflectionAttribute|null {
        $reflection = new ReflectionObject($object);

        $attribute = $reflection->getAttributes($attribute);

        return !empty($attribute) ? $attribute[key($attribute)] : null;
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

        return !empty($attribute) ? $attribute[key($attribute)] : null;
    }

    public static function getAttributeFromMethod(string $className, string $methodName, string $attribute): ReflectionAttribute|null {
        try {
            $reflection = new ReflectionMethod($className, $methodName);
        } catch (ReflectionException) {
            return null;
        }

        $attribute = $reflection->getAttributes($attribute);

        return !empty($attribute) ? $attribute[key($attribute)] : null;
    }

    /** @return ReflectionMethod[] */
    public static function getMethods(string $className, ?string $attributeName = null): array {
        try {
            $methods = (new ReflectionClass($className))->getMethods();

            if (!is_null($attributeName)) {
                $methods = array_filter($methods, function ($method) use ($attributeName) {
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

    public static function initializeWithDefaultProperties(object $currentInstance): object {
        $properties = (new ReflectionObject($currentInstance))->getProperties();

        foreach ($properties as $property) {
            $typeName = $property->getType()->getName();

            if (enum_exists($typeName)) {
                if ($property->getDefaultValue()) {
                    continue;
                }

                if ($property->getType()->allowsNull()) {
                    $property->setValue($currentInstance, null);
                }

                continue;
            }

            if (class_exists($typeName)) {
                $valueInstance = (new ReflectionClass($typeName))->newInstance();

                $property->setValue($currentInstance, self::initializeWithDefaultProperties($valueInstance));
                continue;
            }

            if ($property->getType()->allowsNull() && !$property->getDefaultValue() && $property->getType()
                                                                                                ->allowsNull()) {
                $property->setValue($currentInstance, null);
            }
        }

        return $currentInstance;
    }
}
