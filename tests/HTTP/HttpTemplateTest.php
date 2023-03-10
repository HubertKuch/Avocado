<?php

namespace Avocado\Tests\Unit;

use Avocado\HTTP\ContentType;
use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\HttpTemplate;
use Avocado\HTTP\RequestEntity;
use Avocado\Tests\Unit\Application\MockedApplication;
use JsonPlaceholderTodo;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class HttpTemplateTest extends TestCase {

    public function testGetResponse() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/test-users/");

        MockedApplication::init();

        $response = HttpTemplate::getResponse(TestUser::class);

        self::assertTrue($response->getData()[0] instanceof TestUser);
        self::assertTrue($response->getContentType() == ContentType::APPLICATION_JSON);
        self::assertTrue($response->getStatus() == HTTPStatus::OK);
    }

    public function testGivenValidEntity_whenRequest_thenReturnValidNotParsedData(): void {
        $entity = new RequestEntity(HTTPMethod::GET, 'https://jsonplaceholder.typicode.com/todos/1');
        $response = HttpTemplate::realRequest($entity);

        self::assertSame(HTTPStatus::OK, $response->getStatus());
        self::assertSame(ContentType::APPLICATION_JSON, $response->getContentType());
        self::assertStringContainsStringIgnoringCase("userId", $response->getData());
        self::assertStringContainsStringIgnoringCase("completed", $response->getData());
    }

    public function testGivenValidEntity_whenRequest_thenReturnValidParsedData(): void {
        $entity = new RequestEntity(HTTPMethod::GET, 'https://jsonplaceholder.typicode.com/todos/1');
        $response = HttpTemplate::realRequest($entity, JsonPlaceholderTodo::class);

        self::assertSame(HTTPStatus::OK, $response->getStatus());
        self::assertSame(ContentType::APPLICATION_JSON, $response->getContentType());
        self::assertTrue($response->getData() instanceof JsonPlaceholderTodo);
    }
}
