<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Exceptions\MissingRequestParamException;
use Avocado\Router\AvocadoRouter;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class ParameterProviderTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testParsingValidData(): void {
        $_SERVER['REQUEST_METHOD'] = "POST";
        $_POST['name'] = "test";
        $_POST['age'] = 24;
        AvocadoRouter::useJSON();
        $_SERVER['PHP_SELF'].="/avocado-test/parsing-objects";

        MockedApplication::init();

        self::assertSame('parsed', ob_get_contents());
    }


    /**
     * @runInSeparateProcess
     * */
    public function testShouldThrowInvalidRequest() {
        $_SERVER['REQUEST_METHOD'] = "POST";
        $_POST['name'] = "test";
        AvocadoRouter::useJSON();
        $_SERVER['PHP_SELF'].="/avocado-test/parsing-objects";

        MockedApplication::init();

        self::assertSame('{"message":"Invalid request body.","status":400}', ob_get_contents());
    }

    /**
     * @runInSeparateProcess
     * */
    public function testParsingHeaders() {
        $expected = "application/json";
        $_SERVER['HTTP_CONTENT_TYPE'] = $expected;
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'].="/avocado-test/parsing-headers";

        MockedApplication::init();

        self::assertSame($expected, ob_get_contents());
    }

    public function testParsingRequestParams() {
        $expected = "testParam";
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'].="/avocado-test/parsing-staticparams/$expected";

        MockedApplication::init();

        self::assertSame($expected, ob_get_contents());
    }

    public function testParsingRequiredParams() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'].="/avocado-test/parsing-requiredParam/";
        MockedApplication::init();

        self::assertSame('{"message":"Missing `param` param.","status":400}', ob_get_contents());
    }

    public function testParsingWithDefaultValue() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'].="/avocado-test/parsing-defaultValue/";
        MockedApplication::init();

        self::assertSame('test', ob_get_contents());
    }
}