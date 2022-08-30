<?php

namespace Avocado\Tests\Unit\Application;

use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Avocado\Application\Controller;
use Avocado\Application\Application;
use AvocadoApplication\Mappings\MethodMapping;
use Avocado\AvocadoApplication\Exceptions\MissingAnnotationException;

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
        MockedApplication::init();

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
        MockedApplication::init();

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

        MockedApplication::init();

        self::assertSame('["Get Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPostMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "POST";

        $_SERVER['PHP_SELF'].="/avocado-test";

        MockedApplication::init();

        self::assertSame('["Post Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testDeleteMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "DELETE";

        $_SERVER['PHP_SELF'].="/avocado-test";

        MockedApplication::init();

        self::assertSame('["Delete Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPatchMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "PATCH";

        $_SERVER['PHP_SELF'].="/avocado-test";

        MockedApplication::init();

        self::assertSame('["Patch Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPutMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "PUT";

        $_SERVER['PHP_SELF'].="/avocado-test";

        MockedApplication::init();

        self::assertSame('["Put Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testBaseUrlForController(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/avocado-test/array/";

        MockedApplication::init();

        self::assertSame('["Get Hello World Array"]', ob_get_contents());
    }

    public function testExceptionHandlerResource(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/avocado-test/exception/";

        MockedApplication::init();

        self::assertSame('{"status":400,"message":"test"}', ob_get_contents());
    }

    public function testAutoResponseAfterException(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/avocado-test/exception/auto-response";

        MockedApplication::init();

        self::assertSame('{"message":"auto response","status":409}', ob_get_contents());
    }

    public function testPageNotFound() {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/jdskaild";

        MockedApplication::init();

        self::assertSame('{"message":"Page was not found","status":404}', ob_get_contents());
    }

    public function testExcludedClasses() {
        $_SERVER['REQUEST_METHOD'] = "GET";

        MockedApplication::init();

        $ref = new ReflectionClass(Application::class);

        $classes = $ref->getStaticPropertyValue("declaredClasses");

        self::assertFalse(in_array(TestClassToExclude::class, $classes));
    }

    public function testExcludingAvocadoTestsFromProductionApplication() {
        $this->expectException(MissingAnnotationException::class);

        $_SERVER['REQUEST_METHOD'] = "GET";

        $_ENV['AVOCADO_ENVIRONMENT'] = "PRODUCTION";

        MockedApplication::init();
    }

    public function testParamsInRouter() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'] = "/avocado-test/param/4";

        MockedApplication::init();

        self::assertSame("4", ob_get_contents());
    }
}
