<?php

namespace Avocado\Tests\Unit\Application;


use Exception;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Application\RestController;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\GetMapping;
use AvocadoApplication\Mappings\PutMapping;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Mappings\PostMapping;
use AvocadoApplication\Mappings\PatchMapping;
use AvocadoApplication\Mappings\DeleteMapping;

#[RestController]
#[BaseURL("/avocado-test")]
class MockedController {

    #[Autowired]
    private MockedResource $mockedResource;
    #[Autowired]
    private MockedResourceWithAlternativeName $mockedResourceWithAlternativeName;

    #[GetMapping("/")]
    public function getHelloWorld(AvocadoRequest $req, AvocadoResponse $res): void {
        $res->json(["Get Hello World"]);
    }

    #[GetMapping("/param/:test")]
    public function testGetParam(AvocadoRequest $req, AvocadoResponse $res): void {
        echo $req->params['test'];
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

    #[GetMapping("/alternative-resource-name")]
    public function testResourceWithAlternativeName(AvocadoRequest $req, AvocadoResponse $res) {
        $res->json([$this->mockedResourceWithAlternativeName->getTest()]);
    }

    /**
     * @throws Exception
     */
    #[GetMapping("/exception/auto-response")]
    public function exceptionTestWithAutoResponse() {
        throw new MockedExceptionWithResponseStatus("auto response");
    }
}
