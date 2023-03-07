<?php

namespace Avocado\Tests\Utils;

use Avocado\Utils\Arrays;
use PHPUnit\Framework\TestCase;
use stdClass;

class ArraysTest extends TestCase {

    public function testSimpleIndexOf() {
        $array = [2, 21, 324, 51234, 12, 21, 1, 2134, 4, 10];

        $indexOfTwo = Arrays::indexOf($array, fn($el) => $el === 21);

        self::assertSame(1, $indexOfTwo);
    }

    public function testObjectIndexOf() {
        $key = "test";
        $array = [new stdClass(), new stdClass(), new stdClass(), new stdClass()];
        $matchedEl = new stdClass();
        $matchedEl->{$key} = 120;

        foreach ($array as $obj) $obj->{$key} = rand(0, 100);

        $array[] = $matchedEl;

        $index = Arrays::indexOf($array, fn($el) => $el->{$key} === 120);

        self::assertSame(count($array)-1, $index);
    }
}
