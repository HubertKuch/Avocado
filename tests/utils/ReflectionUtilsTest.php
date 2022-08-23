<?php

namespace Avocado\Utils;

use Avocado\Tests\Unit\TableWithIgnoringType;
use Avocado\Tests\Unit\UserRole;
use PHPUnit\Framework\TestCase;

require "../ORM/AvocadoORMModelTest.php";

class ReflectionUtilsTest extends TestCase {

    public function testModelFieldsToArray() {
        $test = new TableWithIgnoringType(24, UserRole::ADMIN);

        $fields = ReflectionUtils::modelFieldsToArray($test);
        $expected = ["id" => 24, "role" => UserRole::ADMIN];

        self::assertEquals($expected, $fields);
    }
}
