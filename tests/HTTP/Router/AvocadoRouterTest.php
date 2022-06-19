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

    public function testNotFoundRoute() {
        $var = null;

        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME']."/test12";

        AvocadoRouter::GET("/test0", [], fn() => 2);
        AvocadoRouter::PATCH("/test1", [], fn() => 2);
        AvocadoRouter::POST("/test2", [], fn() => 2);
        AvocadoRouter::DELETE("/test3", [], fn() => 2);
        AvocadoRouter::notFoundHandler(function() use(&$var) { $var = "test"; });
        AvocadoRouter::listen();

        self::assertNotNull($var);
    }
}
