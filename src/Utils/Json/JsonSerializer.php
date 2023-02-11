<?php

namespace Avocado\Utils\Json;

use Avocado\AvocadoApplication\Attributes\Json\JsonIgnore;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\Utils\ClassFinder;
use Avocado\Utils\ReflectionUtils;
use Avocado\Utils\StandardObjectMapper;
use ReflectionException;
use stdClass;

class JsonSerializer {

    public static function serialize(array|object $data): string {
        if (is_array($data)) {
            $data = array_map([JsonSerializer::class, "serializeArrayValue"], $data);

            return json_encode($data);
        }

        return json_encode(StandardObjectMapper::objectToPlainStd($data));
    }

    public static function deserialize(string $json, string $targetClass): object|array {
        $plainData = json_decode($json, true);
        $reflection = ClassFinder::getClassReflectionByName($targetClass);

        try {
            if (str_starts_with(trim($json), "[")) {
                return array_map(fn($data) => ReflectionUtils::instanceFromArray($data, $targetClass), $plainData);
            }

            return ReflectionUtils::instanceFromArray($plainData, $targetClass);
        } catch (MissingKeyException|ReflectionException $e) {
            return new stdClass();
        }
    }

    private static function serializeArrayValue(mixed $val): mixed {
        if (is_object($val)) {
            return StandardObjectMapper::objectToPlainStd($val, JsonIgnore::class);
        }

        if (is_array($val)) {
            return self::serialize($val);
        }

        return $val;
    }

}