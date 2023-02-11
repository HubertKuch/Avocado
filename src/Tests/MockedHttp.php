<?php

namespace Avocado\Tests;

use Avocado\HTTP\ContentType;
use Avocado\HTTP\HTTPMethod;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use Avocado\Router\HttpResponse;
use Avocado\Tests\Unit\TestUser;
use Avocado\Utils\Arrays;
use Avocado\Utils\Json\JsonSerializer;
use Avocado\Utils\StandardObjectMapper;

class MockedHttp {

    public static function mockPlainRequest(HTTPMethod $method, string $endpoint): void {
        $_SERVER["REQUEST_METHOD"] = $method->value;
        $_SERVER['PHP_SELF'] = $endpoint;
    }

    public static function getResponse(string $target = ""): ResponseBody {
        $headers = $_SERVER;
        $body = ob_get_contents();
        $status = http_response_code();
        $contentType =  HttpResponse::getContentType();

        if ($contentType) {
            $contentType = str_replace("Content-Type: ", "", $contentType);
            $contentType = trim($contentType);
            if (str_contains($contentType, ";")) {
                $contentType = substr($contentType, 0, strpos($contentType, ";"));
            }
            $contentType = ContentType::from(trim($contentType));
        }

        if ($contentType == ContentType::APPLICATION_JSON && strlen($target) > 0) {
            $body = JsonSerializer::deserialize($body, $target);
        }

        return new ResponseBody($body, HTTPStatus::from($status), $contentType);
    }

}