<?php

namespace application\mock;

use AvocadoApplication\Attributes\Resource;

#[Resource]
class MockInvalidResource {
    public function __construct(int $test) {}
}
