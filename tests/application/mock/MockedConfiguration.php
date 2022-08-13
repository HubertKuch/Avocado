<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Leaf;

class MockedLeafResource {}

#[Configuration]
class MockedConfiguration {

    #[Leaf(name: "mocked_rsc")]
    public function getMockedResource(): MockedLeafResource {
        return new MockedLeafResource();
    }
}