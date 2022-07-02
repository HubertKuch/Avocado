<?php

namespace application\mock;

use Avocado\Application\Application;
use Avocado\Application\RestController;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Mappings\GetMapping;

#[RestController]
class MockedInvalidController {
    #[Autowired]
    private Application $application;

    #[GetMapping("/test")]
    public function getTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["test"]);
    }
}
