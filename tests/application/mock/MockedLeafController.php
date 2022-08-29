<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\RestController;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\GetMapping;
use AvocadoApplication\Attributes\Autowired;
use Avocado\AvocadoApplication\Attributes\Leaf;
use Avocado\AvocadoApplication\Attributes\Configuration;

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
