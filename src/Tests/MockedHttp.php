<?php

namespace Avocado\Tests;

use Avocado\HTTP\ContentType;
use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use Avocado\Router\HttpResponse;
use Avocado\Utils\Arrays;

class MockedHttp {

    public static function mockPlainRequest(HTTPMethod $method, string $endpoint): void {
        $_SERVER["REQUEST_METHOD"] = $method->value;
        $_SERVER['PHP_SELF'] = $endpoint;
    }

    public static function getResponse(): ResponseBody {
        $headers = headers_list();
        $body = ob_get_contents();
        $status = http_response_code();
        $contentType = Arrays::find($headers, fn($header) => str_starts_with("Content-Type: ", $header));

        if ($contentType) {
            $contentType = str_replace("Content-Type: ", "", $contentType);
            $contentType = trim($contentType);
            $contentType = substr($contentType, 0, strpos($contentType, ";"));
            $contentType = ContentType::from(trim($contentType));
        }

        return new ResponseBody($body, HTTPStatus::from($status), $contentType);
    }

}