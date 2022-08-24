<?php

namespace Avocado\Router;

class AvocadoRequest {
    public array $body;
    public array $query;
    public array $cookies;
    public array $params;
    public array $headers;
    public array $locals;
    public string $method;
    public string $fullURL;
    public string $fullURLWithoutQuery;

    private function getAllHeaders(): array{
        $headers = [];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (str_starts_with($name, 'HTTP_')) {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }

        return $headers;
    }

    /**
     * <p>Get authorization token from headers.</p>
     * <p>For example from `Authorization: Bearer 123.456.789` get `123.456.789`.</p>
     * <p>If token was not set then return null.
     * @return string|null
     * */
    public function getAuthorizationToken(): string|null {
        $authorizationHeader = $this->headers['Authorization'] ?? null;
        $bearerLength = 7;

        if ($authorizationHeader) {
            return trim(substr($authorizationHeader, $bearerLength, strlen($authorizationHeader)));
        }

        return null;
    }

    public function getClientIP(): string {
        $ipAddress = "";

        if (array_key_exists("HTTP_CLIENT_IP", $_SERVER)) $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER)) $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (array_key_exists("REMOTE_ADDR", $_SERVER)) $ipAddress = $_SERVER['REMOTE_ADDR'];

        return $ipAddress;
    }

    public function hasHeader(string $header): bool {
        return isset($this->headers[$header]);
    }

    public function hasCookie(string $cookie): bool {
        return isset($this->cookies[$cookie]);
    }

    public function hasParam(string $param): bool {
        return isset($this->params[$param]);
    }

    public function __construct(array $params = array()) {
        $this->body = $_POST;
        $this->query = $_GET;
        $this->cookies = $_COOKIE;
        $this->params = $params;
        $this->headers = self::getAllHeaders();
        $this->method = $_SERVER['REQUEST_METHOD'];
    }
}
