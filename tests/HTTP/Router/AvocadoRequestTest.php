<?php

namespace Avocado\Tests\Unit;

use Avocado\HTTP\HTTPMethod;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoRouter;
use PHPUnit\Framework\TestCase;

class AvocadoRequestTest extends TestCase {

    protected function setUp(): void {
        $_SERVER['PHP_SELF'] = "Standard input code";
    }

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

    public function testGettingPassedClientIPAddress() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = "192.168.1.8";
        $req = new AvocadoRequest();

        self::assertSame($_SERVER['HTTP_X_FORWARDED_FOR'], $req->getClientIP());
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function testGettingUndefinedClientIPAddress() {
        $req = new AvocadoRequest();

        var_dump($req->getClientIP());

        self::assertEmpty($req->getClientIP());
    }

    public function testHeaderWasSet() {
        $req = new AvocadoRequest();
        $req->headers['TEST_HEADER'] = "TEST_VALUE";

        self::assertTrue($req->hasHeader('TEST_HEADER'));
    }

    public function testHeaderWasNotSet() {
        $req = new AvocadoRequest();

        self::assertFalse($req->hasHeader('TEST_HEADER'));
    }

    public function testCookieWasSet() {
        $req = new AvocadoRequest();
        $req->cookies['TEST_COOKIE'] = "TEST_VALUE";

        self::assertTrue($req->hasCookie('TEST_COOKIE'));
    }

    public function testParamWasSet() {
        $req = new AvocadoRequest();
        $req->params['TEST_PARAM'] = "TEST_VALUE";

        self::assertTrue($req->hasParam('TEST_PARAM'));
    }
}
