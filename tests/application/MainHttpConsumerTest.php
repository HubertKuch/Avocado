<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\Tests\Unit\Application\MockedApplication;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class MainHttpConsumerTest extends TestCase {

    private function init(string $endpoint) {
        $_SERVER["PHP_SELF"] = $endpoint;
        $_SERVER['REQUEST_METHOD'] = "GET";
    }

    public function testConsumingAvocadoResponse() {
        $this->init("/avocado-test/consuming/avocado-response");

        MockedApplication::init();

        self::assertSame('["Consumed by avocado response"]', ob_get_contents());
    }

    public function testConsumingReturnedData() {
        $this->init("/avocado-test/consuming/returned-data");

        MockedApplication::init();

        self::assertSame('["Returned data was parsed."]', ob_get_contents());
    }

    public function testConsumingResponseBody() {
        $this->init("/avocado-test/consuming/by-response-body-object");

        MockedApplication::init();

        self::assertSame('["By response object"]', ob_get_contents());
     }
}
