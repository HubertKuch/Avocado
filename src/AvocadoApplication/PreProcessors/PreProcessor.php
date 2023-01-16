<?php

namespace Avocado\AvocadoApplication\PreProcessors;

use Attribute;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class PreProcessor {

    public static function isAnnotated(?ReflectionClass $ref): bool {
        if (!$ref) {
            return false;
        }

        $ann = $ref->getAttributes(PreProcessor::class);

        return !empty($ann);
    }

    public static function getInstance(ReflectionClass $ref) {
        $ann = $ref->getAttributes(PreProcessor::class);

        return $ann[0]->newInstance();
    }
}