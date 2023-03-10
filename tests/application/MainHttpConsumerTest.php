<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\HttpTemplate;
use Avocado\Tests\Unit\Application\MockedApplication;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class MainHttpConsumerTest extends TestCase {

    private function init(string $endpoint) {
        HttpTemplate::mockPlainRequest(HTTPMethod::GET, $endpoint);
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

     public function testConsumingProducesAnnotation() {
         $this->init("/avocado-test/consuming/produces");

         MockedApplication::init();
         self::assertSame('Test consuming produces annotation', ob_get_contents());
     }

     public function testConsumingImage() {
         $this->init("/avocado-test/consuming/image");
         $imageUrl = "https://images.unsplash.com/photo-1481349518771-20055b2a7b24?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1239&q=80";

         $stream = file_get_contents($imageUrl);

         MockedApplication::init();

         self::assertSame($stream, ob_get_contents());
     }
}
