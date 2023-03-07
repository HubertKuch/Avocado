<?php

namespace Avocado\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Utils\Strings;

class StringsTest extends TestCase {

    public function testCamelCaseToUnderscore() {
        $camelCase = "camelCase";
        $underscore = "camel_case";

        $parsed = Strings::camelToUnderscore($camelCase);

        self::assertSame($underscore, $parsed);
    }

}
