<?php

namespace AvocadoApplication\Tests\Unit\Application\DependencyInjection;

use Avocado\Application\Application;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceNotFoundException;
use AvocadoApplication\DependencyInjection\Exceptions\TooMuchResourceConstructorParametersException;
use PHPUnit\Framework\TestCase;

require_once "mock/MockedResource.php";
require_once "mock/MockedController.php";
require_once "mock/MockedApplication.php";
require __DIR__."/mock/MockedDatabaseConfiguration.php";

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class DependencyInjectionServiceTest extends TestCase {
    /**
     * @runInSeparateProcess
     * */
    public function testGetMapping(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/hello-world/di";

        Application::run();

        self::assertSame('["test"]', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testResourceNotFoundException(): void {
        require_once "mock/MockedInvalidController.php";

        $this->expectException(ResourceNotFoundException::class);

        $_SERVER['REQUEST_METHOD'] = "GET";

        $_SERVER['PHP_SELF'].="/test";


        Application::run();
    }

    /**
     * @runInSeparateProcess
     * */
    public function testTooMuchResourceConstructorParametersException(): void {
        require_once "mock/MockInvalidResource.php";

        $this->expectException(TooMuchResourceConstructorParametersException::class);

        Application::run();
    }
}
