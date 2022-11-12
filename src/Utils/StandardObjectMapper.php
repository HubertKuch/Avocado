<?php

namespace Avocado\Utils;

use Avocado\AvocadoApplication\Exceptions\MissingKeyException;

class StandardObjectMapper {

    /**
     * @throws \ReflectionException
     * @throws MissingKeyException
     */
    public static function arrayToObject(array $data, string $target) {
        return ReflectionUtils::instanceFromArray($data, $target);
    }

}