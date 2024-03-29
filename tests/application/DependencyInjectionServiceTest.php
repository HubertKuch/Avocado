<?php

namespace AvocadoApplication\Tests\Unit\Application\DependencyInjection;

use Avocado\Tests\Unit\Application\MockedResourceWithAutowiredProperties;
use PHPUnit\Framework\TestCase;
use Avocado\DataSource\DataSource;
use Avocado\Application\Application;
use Avocado\AvocadoApplication\Leafs\LeafManager;
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

        self::assertTrue(count(array_filter($res, fn($resourceable) => in_array(DataSource::class, $resourceable->getTargetResourceTypes()))) == 1);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testAutowiringPropertiesInResources() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        MockedApplication::init();

        $resource = DependencyInjectionService::getResourceByType(MockedResourceWithAutowiredProperties::class);
        $instance = $resource->getTargetInstance();
        self::assertTrue($instance instanceof MockedResourceWithAutowiredProperties);
        self::assertSame($instance->test(), "test");
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetLeafsByName() {
        $_SERVER['REQUEST_METHOD'] = "GET";

        MockedApplication::init();

        self::assertNotNull(DependencyInjectionService::getResourceByName("testResource"));
    }

    public function testInjectResourceByName() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'] .= "/avocado-test/alternative-resource-name/";

        MockedApplication::init();

        self::assertSame('["TEST"]', ob_get_contents());
    }
}
