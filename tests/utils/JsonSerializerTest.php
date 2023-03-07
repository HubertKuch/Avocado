<?php

namespace Avocado\Tests\Utils\Json;

use Avocado\Utils\Json\JsonSerializer;
use PHPUnit\Framework\TestCase;
use stdClass;

class JsonSerializerTest extends TestCase {

    public function testSerialize() {
        $root = new stdClass();

        $root->int = 2;
        $root->bool = false;
        $root->array = ["test", 4, new stdClass()];

        $nested = new stdClass();
        $nested->test = "test";

        $root->nested = $nested;

        self::assertSame('{"int":2,"bool":false,"array":["test",4,{}],"nested":{"test":"test"}}', JsonSerializer::serialize($root));
    }
}
