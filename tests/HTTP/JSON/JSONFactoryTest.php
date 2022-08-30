<?php

namespace HTTP\JSON;

use PHPUnit\Framework\TestCase;
use Avocado\HTTP\JSON\JSONFactory;


class JSONFactoryTest extends TestCase {
    public function testSerializePrimitive(): void {
        self::assertSame("[2,4,\"test\",false,null]", JSONFactory::serializePrimitive([2, 4, "test", false, null]));
    }

    public function testSerializeObjects(): void {
        $user = new JSONFactoryTestUser("testName", "testPassword");

        self::assertSame('{"username": "testName"}', JSONFactory::serializeObjects($user));
    }

    public function testSerializeWithPrivateProperties(): void {
        $user = new JSONFactoryTestUser("testName", true);

        self::assertSame('{"id": 1,"username": "testName","isAdmin": true}', JSONFactory::serializeObjects($user, true));
    }

    public function testSerializeAssociativeArray(): void {
        $array = [
            "username" => "test",
            "password" => "Test"
        ];

        self::assertSame('{"username":"test","password":"Test"}', JSONFactory::serializePrimitive($array));
    }
}
