<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\RestController;
use Avocado\AvocadoApplication\Middleware\BeforeRoute;
use Avocado\Router\AvocadoResponse;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\GetMapping;

#[BeforeRoute([
    "\Avocado\Tests\Unit\Application\MockedMiddleware::test"
])]
#[RestController]
#[BaseURL("/middleware-test")]
class MockedControllerWithTopLevelMiddleware {

    #[GetMapping("/")]
    public function test(AvocadoResponse $response): AvocadoResponse {
        return $response -> json(["test"]);
    }

}