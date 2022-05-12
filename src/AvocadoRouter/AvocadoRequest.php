<?php

namespace Avocado\Router;

class AvocadoRequest {
    public array $body;
    public array $query;
    public array $cookies;
    public array $params;
    public array $headers;
    public array $locals;

    public function __construct(array $params = array()) {
        $this->body = $_POST;
        $this->query = $_GET;
        $this->cookies = $_COOKIE;
        $this->params = $params;
        $this->headers = self::getAllHeaders();
        $this->method = $_SERVER['REQUEST_METHOD'];
    }
}
