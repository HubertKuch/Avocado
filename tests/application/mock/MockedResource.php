<?php

namespace Avocado\Tests\Unit\Application;

use AvocadoApplication\Attributes\Resource;

#[Resource]
class MockedResource {

    public function __construct() {}

    public function getTest(): string {
        return "test";
    }
}
