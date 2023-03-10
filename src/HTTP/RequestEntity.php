<?php

namespace Avocado\HTTP;


class RequestEntity {
    public function __construct(
        private HTTPMethod $method,
        private string $url,
        private string $body = "",
        private array $headers = []
    ) {}

    public function getMethod(): HTTPMethod {
        return $this->method;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function getHeaders(): array {
        return $this->headers;
    }
}