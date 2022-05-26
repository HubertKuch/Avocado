<?php

namespace Avocado\HTTP\JSON;

use PHPUnit\Framework\TestCase;

class JSONTest extends TestCase {
    public function testParsingObject() {
        $object = new \stdClass();
        $object->test = "Test";
        $object->name = "john";

        $json = new JSON($object);

        self::assertSame('{"test": "Test","name": "john"}', $json->getSerializedData());
    }

    public function testParsingMultipleObjects() {
        $object = new \stdClass();
        $object->test = "Test";
        $object->name = "john";

        $object2 = new \stdClass();
        $object2->test = "Test2";
        $object2->name = "john2";

        $json = new JSON([$object, $object2]);

        self::assertSame('[{"test": "Test","name": "john"},{"test": "Test2","name": "john2"}]', $json->getSerializedData());
    }

    public function testParsingPrimitive() {
        $json = new JSON([2, 4, "TEST", false]);

        self::assertSame('[2,4,"TEST",false]', $json->getSerializedData());
    }

    public function testParsingAssociativeArray() {
        $data = [
            "name" => "John",
            "lastName" => "Doe"
        ];

        $json = new JSON($data);

        self::assertSame('{"name":"John","lastName":"Doe"}', $json->getSerializedData());
    }
}
