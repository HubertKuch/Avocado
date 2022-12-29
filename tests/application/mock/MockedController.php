<?php

namespace Avocado\Tests\Unit\Application;


use Avocado\Application\RestController;
use Avocado\AvocadoApplication\Attributes\Request\RequestBody;
use Avocado\AvocadoApplication\Attributes\Request\RequestHeader;
use Avocado\AvocadoApplication\Attributes\Request\RequestStorageItem;
use Avocado\AvocadoApplication\Exceptions\InvalidRequestBodyException;
use Avocado\AvocadoApplication\Mappings\Produces;
use Avocado\AvocadoApplication\Middleware\BeforeRoute;
use Avocado\AvocadoApplication\Middleware\Next;
use Avocado\HTTP\ContentType;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\Optional;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\DeleteMapping;
use AvocadoApplication\Mappings\GetMapping;
use AvocadoApplication\Mappings\PatchMapping;
use AvocadoApplication\Mappings\PostMapping;
use AvocadoApplication\Mappings\PutMapping;
use Exception;
use http\Client;

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

    #[PostMapping("/parsing-objects")]
    public function parsingObjectTest(
        AvocadoResponse $response,
        #[RequestBody] ObjectToParse $objectShouldBeParsed,
    ) {
        print "parsed";
    }

    #[GetMapping("/parsing-headers")]
    public function parsingHeaders(#[RequestHeader(name: "Content-Type")] string $contentType) {
        print $contentType;
    }

    #[GetMapping("/parsing-staticparams/:param")]
    public function parsingStaticParams(#[RequestParam(name: "param")] string $param) {
        print $param;
    }

    #[GetMapping("/parsing-requiredParam/:param")]
    public function parsingRequiredParamsWhetherMissing(#[RequestParam(name: "param", required: true)] string $param) {
        print $param;
    }

    #[GetMapping("/parsing-defaultValue/")]
    public function parsingParamsWithDefaults(#[RequestParam(name: "param", defaultValue: "test")] string $param) {
        print $param;
    }

    #[GetMapping("/standard-query/")]
    public function parsingQueryVariables(#[RequestQuery(name: "name")] string $name) {
        print $name;
    }

    #[GetMapping("/default-query/")]
    public function parsingDefaultQueryVariables(#[RequestQuery(name: "test", defaultValue: "Targaryen")] string $name) {
        print $name;
    }


    #[GetMapping("/required-query/")]
    public function parsingRequiredQuery(#[RequestQuery(name: "test", required: true)] string $name) {
        print $name;
    }

    public static function customMiddleware(AvocadoRequest $request, AvocadoResponse $response, Next $next) {
        return $next;
    }

    public static function secondMiddleware(AvocadoRequest $request, AvocadoResponse $response, Next $next) {
        $request->locals['user'] = 'Jon';

        return $next;
    }

    public static function storageKeyMiddleware(AvocadoRequest $request, AvocadoResponse $response, Next $next) {
        $request->locals['name'] = "Targaryen";

        return $next;
    }

    public static function middlewareWithException() {
        throw new InvalidRequestBodyException("TEST EXCEPTIONS IN MIDDLEWARE METHODS");
    }

    #[GetMapping("/middleware/")]
    #[BeforeRoute([
        "\Avocado\Tests\Unit\Application\MockedController::customMiddleware",
        "\Avocado\Tests\Unit\Application\MockedController::secondMiddleware",
    ])]
    public function testMiddlewareWorkflow(AvocadoRequest $request) {
        print $request->locals['user'];
    }

    #[GetMapping("/middleware-with-storage-items/")]
    #[BeforeRoute([
        "\Avocado\Tests\Unit\Application\MockedController::storageKeyMiddleware"
    ])]
    public function middlewareWithAttributes(#[RequestStorageItem(name: "name")] string $name) {
        print $name;
    }

    #[GetMapping("/middleware-with-exception/")]
    #[BeforeRoute([
        "\Avocado\Tests\Unit\Application\MockedController::middlewareWithException"
    ])]
    public function middlewareWithExceptions() {}

    #[GetMapping("/optionals-mapping/:testParam2/:testParam")]
    public function testOptionals(
        #[RequestBody(type: ObjectToParse::class)] Optional $optionalRequestBody,
        #[RequestHeader(name: "Accept")] Optional $acceptHeader,
        #[RequestStorageItem(name: "name", defaultValue: "Jon")] Optional $optionalName,
        #[RequestParam(name: "testParam", required: false, defaultValue: "testParamValue")] Optional $optionalParam,
        #[RequestParam(name: "testParam2", required: true)] Optional $optionRequiredValue,
        #[RequestQuery(name: "testQuery1", defaultValue: "testQuery")] Optional $optionalQuery,
        #[RequestQuery(name: "testQuery2", required: true)] Optional $optionalQueryRequired,
    ) {
        var_dump(
            $optionalName->get(),
            $acceptHeader->get(),
            $optionalRequestBody->get(),
            $optionalParam->get(),
            $optionRequiredValue->get(),
            $optionalQuery->get(),
            $optionalQueryRequired->get()
        );
    }

    #[GetMapping("/consuming/by-response-body-object")]
    public function testConsumingResponseBody(): ResponseBody {
        return new ResponseBody(["By response object"], HTTPStatus::OK);
    }

    #[GetMapping("/consuming/avocado-response")]
    public function testConsumingAvocadoResponse(AvocadoResponse $response): AvocadoResponse {
        return $response->json(["Consumed by avocado response"]);
    }

    #[GetMapping("/consuming/returned-data")]
    public function testConsumingReturnedData(): mixed {
        return ["Returned data was parsed."];
    }

    #[GetMapping("/consuming/produces")]
    #[Produces(contentType: ContentType::TEXT_PLAIN)]
    public function testConsumingProducesAnnotation(): string {
        return "Test consuming produces annotation";
    }

    #[GetMapping("/consuming/image")]
    #[Produces(contentType: ContentType::IMAGE_PNG)]
    public function testConsumingImage(): string {
        $url = "https://images.unsplash.com/photo-1481349518771-20055b2a7b24?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1239&q=80";

        $stream = file_get_contents($url);

        if (!$stream) {
            return "";
        }

        return $stream;
    }
}
