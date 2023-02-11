<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Leaf;
use Avocado\AvocadoApplication\Attributes\Configuration;

class MockedLeafResource {
    public function test(): string {
        return "TEST";
    }
}

#[Configuration]
class MockedConfiguration {

    #[Leaf(name: "mocked_rsc")]
    public function getMockedResource(): MockedLeafResource {
        return new MockedLeafResource();
    }
}
