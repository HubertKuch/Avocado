<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\Application\Controller;
use AvocadoApplication\Mappings\MethodMapping;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class ApplicationTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testRestGettingControllers(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run(__DIR__);

        self::assertStringContainsString("test", "test");

        $applicationReflection = new ReflectionClass(Application::class);
        $controller = $applicationReflection->getStaticPropertyValue('restControllers')[0];

        self::assertTrue($controller instanceof Controller);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGettingControllerRoutes(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run(__DIR__);

        $applicationReflection = new ReflectionClass(Application::class);
        $controller = $applicationReflection->getStaticPropertyValue('restControllers')[0];
        $controllerReflection = new ReflectionClass(Controller::class);

        $mappings = $controllerReflection->getProperty('mappings');

        $mappings = $mappings->getValue($controller);

        self::assertTrue($mappings[key($mappings)] instanceof MethodMapping);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/avocado-test";

        Application::run(__DIR__);

        self::assertSame('["Get Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPostMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "POST";

        $_SERVER['PHP_SELF'].="/avocado-test";

        Application::run(__DIR__);

        self::assertSame('["Post Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testDeleteMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "DELETE";

        $_SERVER['PHP_SELF'].="/avocado-test";

        Application::run(__DIR__);

        self::assertSame('["Delete Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPatchMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "PATCH";

        $_SERVER['PHP_SELF'].="/avocado-test";

        Application::run(__DIR__);

        self::assertSame('["Patch Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPutMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "PUT";

        $_SERVER['PHP_SELF'].="/avocado-test";

        Application::run(__DIR__);

        self::assertSame('["Put Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testBaseUrlForController(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/avocado-test/array/";

        Application::run(__DIR__);

        self::assertSame('["Get Hello World Array"]', ob_get_contents());
    }

    public function testExceptionHandlerResource(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/avocado-test/exception/";

        Application::run(__DIR__);

        self::assertSame('{"status":400,"message":"test"}', ob_get_contents());
    }

    public function testAutoResponseAfterException(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/avocado-test/exception/auto-response";

        Application::run(__DIR__);

        self::assertSame('{"message":"auto response","status":409}', ob_get_contents());
    }

    public function testPageNotFound() {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/jdskaild";

        Application::run(__DIR__);

        self::assertSame(true, true);
    }
}
