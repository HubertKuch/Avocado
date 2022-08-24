<?php

namespace AvocadoApplication\Tests\Unit\Application\DependencyInjection;

use Avocado\Application\Application;
use AvocadoApplication\DependencyInjection\Exceptions\ResourceNotFoundException;
use AvocadoApplication\DependencyInjection\Exceptions\TooMuchResourceConstructorParametersException;
use PHPUnit\Framework\TestCase;

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

        $_SERVER['PHP_SELF'].="/avocado-test/di";

        Application::run(__DIR__);

        self::assertSame('["test"]', ob_get_contents());
    }
}
