<?php

namespace AvocadoApplication\Tests\Unit\Application\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Avocado\DataSource\DataSource;
use Avocado\Tests\Unit\Application\MockedApplication;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;

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

        $_SERVER['PHP_SELF'] .= "/avocado-test/di";

        MockedApplication::init();

        self::assertSame('["test"]', ob_get_contents());

    }

    /**
     * @runInSeparateProcess
     * */
    public function testIsOnlyOnceResourceInstance() {
        $_SERVER['REQUEST_METHOD'] = "GET";

        MockedApplication::init();

        $res = DependencyInjectionService::getResources();

        self::assertTrue(count(array_filter($res, fn($resourceable) => $resourceable->getTargetResourceClass() == DataSource::class)) == 1);
    }
}
