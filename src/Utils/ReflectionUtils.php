<?php

namespace Avocado\Utils;

use Avocado\ORM\Attributes\Field;
use ReflectionClass;

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
}
