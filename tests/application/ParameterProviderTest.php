<?php

namespace Avocado\Tests\Unit\Application;

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

        self::assertStringContainsString('"message":"Invalid request body.","status":400', ob_get_contents());
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

        self::assertStringContainsString('"message":"Missing `param` param.","status":400', ob_get_contents());
    }

    public function testParsingWithDefaultValue() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'].="/avocado-test/parsing-defaultValue/";
        MockedApplication::init();

        self::assertSame('test', ob_get_contents());
    }

    public function testParsingStandardQuery() {
        $expected = "Jon Snow";
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'] = "/avocado-test/standard-query?name=$expected";
        MockedApplication::init();

        self::assertSame($expected, ob_get_contents());
    }

    public function testParsingDefaultQuery() {
        $expected = "Targaryen";
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'] = "/avocado-test/default-query/";
        MockedApplication::init();

        self::assertSame($expected, ob_get_contents());
    }

    public function testParsingRequiredQuery() {
        $expected = '"message":"Missing `test` query param.","status":400';
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'] = "/avocado-test/required-query/";
        MockedApplication::init();

        self::assertStringContainsString($expected, ob_get_contents());
    }

    public function testProvidingOptionals() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['PHP_SELF'] = "/avocado-test/optionals-mapping/4/";
        MockedApplication::init();

        self::assertStringContainsStringIgnoringCase("jon", ob_get_contents());
        self::assertStringContainsStringIgnoringCase("4", ob_get_contents());
        self::assertStringContainsStringIgnoringCase("NULL", ob_get_contents());
        self::assertStringContainsStringIgnoringCase("testParamValue", ob_get_contents());
        self::assertStringContainsStringIgnoringCase("testQuery", ob_get_contents());
    }
}