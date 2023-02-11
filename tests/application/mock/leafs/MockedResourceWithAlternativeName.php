<?php

namespace Avocado\Tests\Unit\Application;

use AvocadoApplication\Attributes\Resource;

#[Resource("TEST_RESOURCE")]
class MockedResourceWithAlternativeName {

    public function __construct() {}

    public function getTest(): string {
        return "TEST";
    }
}
