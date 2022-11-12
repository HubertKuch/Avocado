<?php

namespace Avocado\Utils;

use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\Tests\Unit\Application\ObjectToParse;
use PHPUnit\Framework\TestCase;

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
}
