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

    /**
     * @description Finds a element which matches a $callback function
     * @param array $array
     * @param callable $callback
     * @return mixed Returns a item
     * */
    public static function find(array $array, callable $callback): mixed {
        $index = self::indexOf($array, $callback);

        if ($index == null) {
            return null;
        }

        return $array[$index];
    }

}