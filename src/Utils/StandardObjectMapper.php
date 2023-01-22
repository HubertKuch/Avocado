<?php

namespace Avocado\Utils;

use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use ReflectionObject;
use stdClass;

class StandardObjectMapper {

    /**
     * @throws \ReflectionException
     * @throws MissingKeyException
     */
    public static function arrayToObject(array $data, string $target) {
        return ReflectionUtils::instanceFromArray($data, $target);
    }


    public static function objectToPlainStd(object $object, ?string $ignorePropertiesAnnotatedWith = null): stdClass {
        try {
            $objectRef = new ReflectionObject($object);
            $properties = $objectRef->getProperties();
            $root = new stdClass();

            foreach ($properties as $property) {
                if (
                    ($ignorePropertiesAnnotatedWith && !AnnotationUtils::isAnnotated($property, $ignorePropertiesAnnotatedWith)) ||
                    !$ignorePropertiesAnnotatedWith
                ) {
                    $value = $property->getValue($object);

                    if (is_object($value)) {
                        $value = self::objectToPlainStd($value, $ignorePropertiesAnnotatedWith);
                    }

                    $root->{$property->getName()} = $value;
                }
            }

            return $root;
        } catch (\Exception) {
            return new stdClass();
        }
    }

}