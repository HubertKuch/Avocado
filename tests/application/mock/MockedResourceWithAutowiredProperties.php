<?php

namespace Avocado\Tests\Unit\Application;

use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\Resource;

#[Resource]
class MockedResourceWithAutowiredProperties {

    #[Autowired]
    private MockedResource $anotherDependency;

    public function __construct() {}

    public function test(): string {
        return $this->anotherDependency->getTest();
    }
}