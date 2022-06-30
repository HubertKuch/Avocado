<?php

namespace Avocado\Tests\Unit\Application;


use Avocado\Application\RestController;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\DeleteMapping;
use AvocadoApplication\Mappings\GetMapping;
use AvocadoApplication\Mappings\PatchMapping;
use AvocadoApplication\Mappings\PostMapping;
use AvocadoApplication\Mappings\PutMapping;

#[BaseURL("/hello-world")]
#[RestController]
class MockedController {

    #[GetMapping("/")]
    public static function getHelloWorld(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Get Hello World"]);
    }

    #[GetMapping("/array/")]
    public static function getTestArray(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Get Hello World Array"]);
    }

    #[PostMapping("/")]
    public static function createTestArray(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Post Hello World"]);
    }

    #[DeleteMapping("/")]
    public static function deleteTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Delete Hello World"]);
    }

    #[PatchMapping("/")]
    public static function patchTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Patch Hello World"]);
    }

    #[PutMapping("/")]
    public static function putTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Put Hello World"]);
    }
}
