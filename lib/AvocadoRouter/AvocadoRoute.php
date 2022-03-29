<?php

namespace Avocado\Router;

class AvocadoRoute {
    private string $method;
    private string $endpoint;

    public function __construct(string $method, string $endpoint) {
        $this->endpoint = $endpoint;
        $this->method = $method;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getEndpoint(): string {
        return $this->endpoint;
    }
}