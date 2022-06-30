<?php

namespace Avocado\Tests\Unit\Application;


use Avocado\Application\RestController;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use AvocadoApplication\Mappings\DeleteMapping;
use AvocadoApplication\Mappings\GetMapping;
use AvocadoApplication\Mappings\PatchMapping;
use AvocadoApplication\Mappings\PostMapping;
use AvocadoApplication\Mappings\PutMapping;

#[RestController]
class MockedController {

    #[GetMapping("/hello-world")]
    public static function getHelloWorld(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Get Hello World"]);
    }

    #[PostMapping("/hello-world")]
    public static function createTestArray(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Post Hello World"]);
    }

    #[DeleteMapping("/hello-world")]
    public static function deleteTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Delete Hello World"]);
    }

    #[PatchMapping("/hello-world")]
    public static function patchTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Patch Hello World"]);
    }

    #[PutMapping("/hello-world")]
    public static function putTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Put Hello World"]);
    }
}
