<?php

namespace Avocado\Router;

use Avocado\HTTP\HTTPMethod;

class AvocadoRoute {
    private string $method;
    private string $endpoint;

    public function __construct(string|HTTPMethod $method, string $endpoint) {
        $this->endpoint = $endpoint;
        $this->method = $method instanceof HTTPMethod ? $method->value : $method;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getEndpoint(): string {
        return $this->endpoint;
    }

    public static function clearEndpoint(string $endpoint): string {
        if ($endpoint[0] === "/") $endpoint = substr($endpoint, 1);
        if (strlen($endpoint > 0) && $endpoint[-1] === "/") $endpoint = substr($endpoint, 0, -1);
        return trim($endpoint);
    }

    public static function createGet(string $endpoint): AvocadoRoute {
        return new AvocadoRoute(HTTPMethod::GET, self::clearEndpoint($endpoint));
    }

    public static function createPost(string $endpoint): AvocadoRoute {
        return new AvocadoRoute(HTTPMethod::POST, self::clearEndpoint($endpoint));
    }

    public static function createPatch(string $endpoint): AvocadoRoute {
        return new AvocadoRoute(HTTPMethod::PATCH, self::clearEndpoint($endpoint));
    }

    public static function createPut(string $endpoint): AvocadoRoute {
        return new AvocadoRoute(HTTPMethod::PUT, self::clearEndpoint($endpoint));
    }

    public static function createDelete(string $endpoint): AvocadoRoute {
        return new AvocadoRoute(HTTPMethod::DELETE, self::clearEndpoint($endpoint));
    }
}
