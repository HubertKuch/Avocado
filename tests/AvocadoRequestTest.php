<?php

namespace Avocado\Tests\Unit;

use Avocado\HTTP\HTTPMethod;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoRouter;
use PHPUnit\Framework\TestCase;

class AvocadoRequestTest extends TestCase {
    public function testOverridingMethodByHiddenBodyPropertyToDELETE() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_POST['_method'] = HTTPMethod::DELETE->value;

        AvocadoRouter::listen();
        $excepted = HTTPMethod::DELETE->value;

        self::assertSame($excepted, $_SERVER['REQUEST_METHOD']);
    }

    public function testOverridingMethodByHiddenBodyPropertyToPUT() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_POST['_method'] = HTTPMethod::PUT->value;

        AvocadoRouter::listen();
        $excepted = HTTPMethod::PUT->value;

        self::assertSame($excepted, $_SERVER['REQUEST_METHOD']);
    }

    public function testGettingAuthorizationToken() {
        $excepted = "123.456.789";

        $req = new AvocadoRequest();
        $req->headers['Authorization'] = "Bearer   $excepted";

        self::assertSame($excepted, $req->getAuthorizationToken());
    }
}
