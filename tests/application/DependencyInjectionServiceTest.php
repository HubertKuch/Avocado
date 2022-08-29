<?php

namespace AvocadoApplication\Tests\Unit\Application\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Avocado\Tests\Unit\Application\MockedApplication;

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

        MockedApplication::init(__DIR__);

        self::assertSame('["test"]', ob_get_contents());
    }
}
