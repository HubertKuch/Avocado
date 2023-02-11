<?php

namespace Avocado\Tests;

use Avocado\HTTP\HTTPMethod;

class MockedHttp {

    public static function mockPlainRequest(HTTPMethod $method, string $endpoint): void {
        $_SERVER["REQUEST_METHOD"] = $method->value;
        $_SERVER['PHP_SELF'] = $endpoint;
    }

}