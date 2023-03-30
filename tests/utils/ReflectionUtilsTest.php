<?php

namespace Avocado\Tests\Utils;

use Avocado\Utils\ReflectionUtils;
use PHPUnit\Framework\TestCase;
use Avocado\Tests\Unit\UserRole;
use Avocado\Tests\Unit\TableWithIgnoringType;

class ReflectionUtilsTest extends TestCase {

    public function testModelFieldsToArray() {
        $test = new TableWithIgnoringType(24, UserRole::ADMIN);

        $fields = ReflectionUtils::modelFieldsToArray($test, true);
        $expected = ["id" => 24, "role" => 'admin'];

        self::assertEquals($expected, $fields);
    }
}
