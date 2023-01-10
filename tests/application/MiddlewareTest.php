<?php

namespace Avocado\Tests\Unit\Application;

use Exception;
use PHPUnit\Framework\TestCase;


/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class MiddlewareTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testBasicMiddleware() {
        $_SERVER['PHP_SELF'] = "/avocado-test/middleware/";
        $_SERVER['REQUEST_METHOD'] = "GET";

        MockedApplication::init();

        self::assertSame("Jon", ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testProvidingRequestAttributes() {
        $_SERVER['PHP_SELF'] = "/avocado-test/middleware-with-storage-items/";
        $_SERVER['REQUEST_METHOD'] = "GET";

        MockedApplication::init();

        self::assertSame("Targaryen", ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testThrowingExceptionsInMiddlewares() {
        $_SERVER['PHP_SELF'] = "/avocado-test/middleware-with-exception/";
        $_SERVER['REQUEST_METHOD'] = "GET";

        MockedApplication::init();

        self::assertStringContainsString('"message":"TEST EXCEPTIONS IN MIDDLEWARE METHODS","status":400', ob_get_contents());
    }

    public function testClassLevelMiddleware() {
        $_SERVER['PHP_SELF'] = "/middleware-test/";
        $_SERVER['REQUEST_METHOD'] = "GET";

        MockedApplication::init();

        self::assertStringContainsString("Exception", ob_get_contents());
    }
}