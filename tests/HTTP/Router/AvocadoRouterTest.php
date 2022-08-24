<?php

namespace Avocado\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Avocado\Router\AvocadoRouter;
use ReflectionClass;

class AvocadoRouterTest extends TestCase {
    public function testGettingRouteEndpoint() {
        $ref = new ReflectionClass(AvocadoRouter::class);

        AvocadoRouter::GET("/test", [], function(){});

        $excepted = "test";

        self::assertSame($excepted, $ref->getStaticPropertyValue("routesStack")[0]["ROUTE"]->getEndpoint());
    }

    public function testGettingRouteHTTPMethod() {
        $ref = new ReflectionClass(AvocadoRouter::class);

        AvocadoRouter::GET("/test", [], function(){});

        $excepted = "GET";

        self::assertSame($excepted, $ref->getStaticPropertyValue("routesStack")[0]["ROUTE"]->getMethod());
    }
}
