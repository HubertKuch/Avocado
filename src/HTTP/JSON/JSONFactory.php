<?php

namespace Avocado\HTTP\JSON;

use ReflectionObject;
use ReflectionProperty;
use Exception;

class JSONFactory {
    /**
     * @param array|object $data
     * @param bool $withPrivateProperties
     * @return string
     */
    public static function serializePrimitive(array $data): string {
        return json_encode($data);
    }

    /**
     * @param array|object $data
     * @param bool $withPrivateProperties
     * @return string
     */
    public static function serializeObjects(array|object $data, bool $withPrivateProperties = false): string {
        $serializedData = "";

        if (gettype($data) === "array") {
            $serializedData .= "[";
            foreach ($data as $object) {
                $serializedData .= self::serializeObject($object, $withPrivateProperties).",";
            }

            return preg_replace("/,$/", '', $serializedData)."]";
        }

        $serializedData .= self::serializeObject($data, $withPrivateProperties);

        return $serializedData;
    }

    /**
     * @param object $object
     * @param ReflectionProperty $property
     * @return string
     */
    private static function processProperty(object $object, ReflectionProperty $property): string {
        return match ($property->getType()?->getName()) {
            "bool" => sprintf('"%s": %s', $property->getName(), $property->isInitialized($object) ? ($property->getValue($object) == 1 ? "true" : "false") : "null"),
            "null", "int", "double" => sprintf('"%s": %s', $property->getName(), $property->isInitialized($object) ? $property->getValue($object) : "null"),
            default => sprintf('"%s": "%s"', $property->getName(), $property->isInitialized($object) ? $property->getValue($object) : null),
        };
    }

    /**
     * @param object $object
     * @param bool $withPrivateProperties
     * @return string
     */
    private static function serializeObject(object $object, bool $withPrivateProperties): string {
        $validData = "{";

        try {
            $ref = new ReflectionObject($object);
            $properties = $ref->getProperties();

            foreach ($properties as $property) {
                if (($property->isPrivate() || $property->isProtected()) && $withPrivateProperties) {
                    $validData .= self::processProperty($object, $property).",";
                } else if ($property->isPublic()) {
                    $validData .= self::processProperty($object, $property).",";
                }
            }
        } catch (Exception $e) {
            return "{}";
        }

        return preg_replace("/,$/", '', $validData)."}";
    }
}
