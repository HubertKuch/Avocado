<?php

namespace Avocado\Tests\Utils;

use Avocado\AvocadoApplication\Attributes\Json\JsonIgnore;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\Tests\Unit\Application\ObjectToParse;
use Avocado\Utils\StandardObjectMapper;
use PHPUnit\Framework\TestCase;


class TestClassToParse {
    public function __construct(
        private string $id,
        private int $test,
        #[JsonIgnore]
        private string $ignored,
        private NestedClassToParse $nested
    ) {}
}

class NestedClassToParse {
    public function __construct(
        private string $nestedTest,
        #[JsonIgnore]
        private int $nestedIgnored
    ) {}
}


class StandardObjectMapperTest extends TestCase {

    public function testArrayToObject() {
        $data = [
            "name" => "test",
            "age" => 21
        ];

        $instance = StandardObjectMapper::arrayToObject($data, ObjectToParse::class);
        self::assertNotNull($instance);
        self::assertNotNull($instance->getAge());
        self::assertNotNull($instance->getName());
    }

    public function testShouldThrowMissingKey() {
        $this->expectException(MissingKeyException::class);
        $data = [
            "name" => "test",
        ];

        $instance = StandardObjectMapper::arrayToObject($data, ObjectToParse::class);
    }


    public function testParsingObjectToStd() {
        $nested = new NestedClassToParse("nested_test", 2);
        $root = new TestClassToParse("id", 21, "ignored", $nested);
        $std = StandardObjectMapper::objectToPlainStd($root, JsonIgnore::class);

        $expected = ["id" => "id", "test" => 21, "nested" => ["nestedTest" => "nested_test"]];
        $got = get_object_vars($std);
        $got["nested"] = get_object_vars($std->nested);

        self::assertEquals($expected, $got);
    }
}
