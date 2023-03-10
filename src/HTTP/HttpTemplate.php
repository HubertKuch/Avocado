<?php

namespace Avocado\HTTP;

use Avocado\Router\HttpResponse;
use Avocado\Utils\Json\JsonSerializer;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class HttpTemplate {

    public static function mockPlainRequest(HTTPMethod $method, string $endpoint): void {
        $_SERVER["REQUEST_METHOD"] = $method->value;
        $_SERVER['PHP_SELF'] = $endpoint;
    }

    public static function realRequest(RequestEntity $entity, string $target = "", bool $autoFillServerData = false): ResponseBody {
        $httpClient = match ($autoFillServerData) {
            true => new Client(["base_uri" => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'], 'verify', false, 'exceptions' => false]),
            false => new Client(["base_uri" => "", 'verify', false, 'exceptions' => false]),
        };

        $response = $httpClient->request($entity->getMethod()->value, $entity->getUrl(), [
            "body" => $entity->getBody(),
            "headers" => $entity->getHeaders()
        ]);

        $responseBody = $response->getBody()->getContents();
        $rawContentType = $response->getHeader('Content-Type')[0] ?? '';
        $rawContentType = substr($rawContentType, 0, strpos($rawContentType, ";") ?: null);
        $contentType = ContentType::from($rawContentType);
        $status = HTTPStatus::from($response->getStatusCode());

        if (str_starts_with($response->getHeader("Content-Type")[0] ?? "", "application/json") && strlen($target) > 0) {
            $responseBody = JsonSerializer::deserialize($responseBody, $target);
        }

        return new ResponseBody($responseBody, $status, $contentType, $target);
    }

    public static function getResponse(string $target = ""): ResponseBody {
        $headers = $_SERVER;
        $body = ob_get_contents();
        $status = http_response_code();
        $contentType = HttpResponse::getContentType();

        if ($contentType) {
            $contentType = str_replace("Content-Type: ", "", $contentType);
            $contentType = trim($contentType);
            if (str_contains($contentType, ";")) {
                $contentType = substr($contentType, 0, strpos($contentType, ";"));
            }
            $contentType = ContentType::from(trim($contentType)) ?? '';
        }

        if ($contentType == ContentType::APPLICATION_JSON && strlen($target) > 0) {
            $body = JsonSerializer::deserialize($body, $target);
        }

        return new ResponseBody($body, HTTPStatus::from($status), $contentType);
    }
}