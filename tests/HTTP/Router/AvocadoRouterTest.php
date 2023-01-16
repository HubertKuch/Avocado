<?php

namespace Avocado\Tests\Unit;

use Avocado\Application\Application;
use Avocado\Tests\Unit\Application\MockedApplication;
use Avocado\Tests\Unit\Application\UriMatchingMockedApplication;
use Avocado\Utils\AvocadoClassFinderUtil;
use Composer\Autoload\ClassLoader;
use ReflectionClass;
use PHPUnit\Framework\TestCase;
use Avocado\Router\AvocadoRouter;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class AvocadoRouterTest extends TestCase {
    public function testGettingRouteEndpoint() {
        $ref = new ReflectionClass(AvocadoRouter::class);

        AvocadoRouter::GET("/test", [], function(){}, ["req", "res"]);

        $excepted = "test";

        self::assertSame($excepted, $ref->getStaticPropertyValue("routesStack")[0]["ROUTE"]->getEndpoint());
    }

    public function testGettingRouteHTTPMethod() {
        $ref = new ReflectionClass(AvocadoRouter::class);

        AvocadoRouter::GET("/test", [], function(){}, ["req", "res"]);

        $excepted = "GET";

        self::assertSame($excepted, $ref->getStaticPropertyValue("routesStack")[0]["ROUTE"]->getMethod());
    }
}
