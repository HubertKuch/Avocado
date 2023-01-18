<?php

namespace Utils;

class Strings {

    public static function camelToUnderscore(string $string): string {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1_', $string));
    }

}