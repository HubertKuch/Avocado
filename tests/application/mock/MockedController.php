<?php

namespace Avocado\Tests\Unit\Application;


use Avocado\Application\RestController;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\DeleteMapping;
use AvocadoApplication\Mappings\GetMapping;
use AvocadoApplication\Mappings\PatchMapping;
use AvocadoApplication\Mappings\PostMapping;
use AvocadoApplication\Mappings\PutMapping;
use Exception;

#[RestController]
#[BaseURL("/avocado-test")]
class MockedController {
    #[Autowired]
    private MockedResource $mockedResource;

    #[GetMapping("/")]
    public function getHelloWorld(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Get Hello World"]);
    }

    #[GetMapping("/di")]
    public function getDITest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json([$this->mockedResource->getTest()]);
    }

    #[GetMapping("/array/")]
    public function getTestArray(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Get Hello World Array"]);
    }

    #[PostMapping("/")]
    public function createTestArray(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Post Hello World"]);
    }

    #[DeleteMapping("/")]
    public function deleteTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Delete Hello World"]);
    }

    #[PatchMapping("/")]
    public function patchTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Patch Hello World"]);
    }

    #[PutMapping("/")]
    public function putTest(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Put Hello World"]);
    }

    /**
     * @throws Exception
     */
    #[GetMapping("/exception")]
    public function exceptionTest() {
        throw new MockedException("test");
    }

    /**
     * @throws Exception
     */
    #[GetMapping("/exception/auto-response")]
    public function exceptionTestWithAutoResponse() {
        throw new MockedExceptionWithResponseStatus("auto response");
    }
}
