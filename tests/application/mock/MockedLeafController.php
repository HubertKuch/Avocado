<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\RestController;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Leaf;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\GetMapping;

class Test {

}

#[Configuration]
class MockedConfigurationLeaf {

    #[Leaf]
    public function test(): Test {
        return new Test();
    }
}

#[RestController]
#[BaseURL("/avocado-test")]
class MockedLeafController {

    #[Autowired]
    private Test $resource;

    #[GetMapping("/")]
    public function getJSON() {}
}
