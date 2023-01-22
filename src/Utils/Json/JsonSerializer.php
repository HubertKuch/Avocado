<?php

namespace Avocado\Utils\Json;

use Avocado\AvocadoApplication\Attributes\Json\JsonIgnore;
use Avocado\Utils\StandardObjectMapper;

class JsonSerializer {

    public static function serialize(array|object $data): string {
        if (is_array($data)) {
            $data = array_map([JsonSerializer::class, "serializeArrayValue"], $data);

            return json_encode($data);
        }

        return json_encode(StandardObjectMapper::objectToPlainStd($data));
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