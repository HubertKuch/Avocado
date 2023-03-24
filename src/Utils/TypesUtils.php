<?php

namespace Avocado\Utils;

class TypesUtils {

    public static function stringContainsPrimitiveType(string $string): bool {
        return $string === "bool" || $string === "string" || $string === "int" || $string === "float" || $string === "integer" || $string === "double";
    }

}