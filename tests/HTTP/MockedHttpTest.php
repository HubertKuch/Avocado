<?php

namespace Avocado\Tests;

use Avocado\HTTP\ContentType;
use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\HTTPStatus;
use Avocado\Tests\Unit\Application\MockedApplication;
use Avocado\Tests\Unit\TestUser;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class MockedHttpTest extends TestCase {

    public function testGetResponse() {
        MockedHttp::mockPlainRequest(HTTPMethod::GET, "/avocado-test/test-users/");

        MockedApplication::init();

        $response = MockedHttp::getResponse(TestUser::class);

        self::assertTrue($response->getData()[0] instanceof TestUser);
        self::assertTrue($response->getContentType() == ContentType::APPLICATION_JSON);
        self::assertTrue($response->getStatus() == HTTPStatus::OK);
    }

}
