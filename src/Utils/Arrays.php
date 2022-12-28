<?php

namespace Avocado\Utils;

class Arrays {

    /**
     * @description Index ofs first element with first `true` value from callback function
     * @param array $array
     * @param callable $callback Callback accepts an current element must return a boolean
     * *@return int|null Index of element. Returns null if no one elements matches callback
     */
    public static function indexOf(array $array, callable $callback): ?int {
        for ($index = 0; $index < count($array); $index++) {
            $res = $callback($array[$index]);

            if ($res) {
                return $index;
            }
        }

        return null;
    }

}