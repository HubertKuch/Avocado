<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\Application\Controller;
use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\HttpTemplate;
use AvocadoApplication\Mappings\MethodMapping;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;
use function PHPUnit\Framework\assertSame;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class ControllerTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testRestGettingControllers(): void {
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
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/");
        MockedApplication::init();

        self::assertSame('["Get Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPostMapping(): void {
        HttpTemplate::mockPlainRequest(HTTPMethod::POST, "/avocado-test");
        MockedApplication::init();

        self::assertSame('["Post Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testDeleteMapping(): void {
        HttpTemplate::mockPlainRequest(HTTPMethod::DELETE, "/avocado-test");
        MockedApplication::init();

        self::assertSame('["Delete Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPatchMapping(): void {
        HttpTemplate::mockPlainRequest(HTTPMethod::PATCH, "/avocado-test");
        MockedApplication::init();

        self::assertSame('["Patch Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPutMapping(): void {
        HttpTemplate::mockPlainRequest(HTTPMethod::PUT, "/avocado-test");
        MockedApplication::init();

        self::assertSame('["Put Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testBaseUrlForController(): void {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/array/");
        MockedApplication::init();

        self::assertSame('["Get Hello World Array"]', ob_get_contents());
    }

    public function testExceptionHandlerResource(): void {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/exception/");
        MockedApplication::init();

        self::assertSame('{"status":400,"message":"test"}', ob_get_contents());
    }

    public function testAutoResponseAfterException(): void {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/exception/auto-response");
        MockedApplication::init();

        self::assertStringContainsString('"message":"auto response","status":409', ob_get_contents());
    }

    public function testPageNotFound() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/random-page/");
        MockedApplication::init();

        self::assertSame('{"message":"Page was not found","status":404}', ob_get_contents());
    }

    public function testParamsInRouter() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/param/4");
        MockedApplication::init();

        self::assertSame("4", ob_get_contents());
    }

    public function testGetMainDir() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/param/4");
        MockedApplication::init();
        self::assertTrue(is_dir(Application::getProjectDirectory()));
    }

    public function testGetConfiguration() {
        MockedApplication::init();
        self::assertNotEmpty(Application::getConfiguration()->getConfigurations());
    }

    public function testUploadingFiles() {
        $_FILES = [
            "file" => [
                "name" => ["test.png"],
                "type" => ["image/png"],
                "tmp_name" => ["/tmp/test"],
                "error" => [0],
                "size" => [123]
            ]
        ];

        HttpTemplate::mockPlainRequest(HTTPMethod::POST, "/avocado-test/validate-file");

        MockedApplication::init();

        self::assertSame("Uploaded", ob_get_contents());
    }

    public function testMovingFiles() {
        $filenamePath = sys_get_temp_dir() . "/another_temp_file.txt";

        try {
            $tempFile = tempnam(sys_get_temp_dir(), "temp_file.test");

            $data = "Test";
            file_put_contents($tempFile, $data, FILE_APPEND);

            $_FILES = [
                "file" => [
                    "name" => ["test.png"],
                    "type" => ["image/png"],
                    "tmp_name" => [$tempFile],
                    "error" => [0],
                    "size" => [123]
                ]
            ];

            HttpTemplate::mockPlainRequest(HTTPMethod::POST, "/avocado-test/upload-file");

            MockedApplication::init();

            self::assertTrue(file_exists($filenamePath));
            self::assertSame("Uploaded", ob_get_contents());
            self::assertSame($data, file_get_contents($filenamePath));

        } finally {
            unlink($filenamePath);
        }
    }

    public function testErrorCatching() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/error-catching");
        try {
            MockedApplication::init();
            assertSame(true, true);
        } catch (Throwable) {
            assertSame(true, false);
        }
    }

    public function testParsingEmptyArray() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/empty-array");
        MockedApplication::init();

        assertSame("[]", ob_get_contents());
    }

    public function testInterceptor() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/interceptor-test");
        MockedApplication::init();

        self::assertStringContainsString("Hello from interceptor", ob_get_contents());
    }

    public function testParsingPrivateProperties() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/private-properties");
        MockedApplication::init();

        self::assertStringContainsString('{"test":4}', ob_get_contents());
    }

    public function testStatusCode() {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, "/avocado-test/exception/auto-response");
        MockedApplication::init();

        assertSame(409, http_response_code());
    }
}
