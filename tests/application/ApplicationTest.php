<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\Application\Controller;
use AvocadoApplication\Mappings\MethodMapping;
use PHPUnit\Framework\TestCase;

require __DIR__."/mock/MockedApplication.php";
require __DIR__."/mock/MockedController.php";


class ApplicationTest extends TestCase {
    /**
     * @runInSeparateProcess
     * */
    public function testRestGettingControllers(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run();

        $applicationReflection = new \ReflectionClass(Application::class);
        $controller = $applicationReflection->getStaticPropertyValue('restControllers')[0];

        self::assertTrue($controller instanceof Controller);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGettingControllerRoutes(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run();

        $applicationReflection = new \ReflectionClass(Application::class);
        $controller = $applicationReflection->getStaticPropertyValue('restControllers')[0];
        $controllerReflection = new \ReflectionClass(Controller::class);

        $mappings = $controllerReflection->getProperty('mappings');

        self::assertTrue($mappings->getValue($controller)[0] instanceof MethodMapping);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/hello-world";

        Application::run();

        self::assertSame('["Get Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPostMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "POST";

        $_SERVER['PHP_SELF'].="/hello-world";

        Application::run();

        self::assertSame('["Post Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testDeleteMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "DELETE";

        $_SERVER['PHP_SELF'].="/hello-world";

        Application::run();

        self::assertSame('["Delete Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPatchMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "PATCH";

        $_SERVER['PHP_SELF'].="/hello-world";

        Application::run();

        self::assertSame('["Patch Hello World"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testPutMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "PUT";

        $_SERVER['PHP_SELF'].="/hello-world";

        Application::run();

        self::assertSame('["Put Hello World"]', ob_get_contents());
    }
}
