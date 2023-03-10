<?php

namespace Avocado\Router;

use Avocado\HTTP\HTTPStatus;

class HttpResponse {

    private static ?string $contentType = null;

    public function write($data): HttpResponse {
        echo $data;

        return $this;
    }

    public function withStatus(HTTPStatus|int $status): HttpResponse {
        http_response_code($status instanceof HTTPStatus ? $status->value : $status);

        return $this;
    }

    public function withCookie(string $key, string $value, ?array $options = array()): HttpResponse {
        if (!empty($options)) {
            setcookie(
                $key,
                $value,
                $options['expire'] ?? 0,
                $options['path'] ?? '',
                $options['domain'] ?? '',
                $options['secure'] ?? false,
                $options['httponly'] ?? false
            );

        }

        if (!isset($this->cookies[$key])) {
            setcookie($key, $value);
        }

        return $this;
    }

    public function json(object|array $data): HttpResponse {
        $this->setHeader("Content-Type", "application/json; charset=utf-8");
        echo json_encode($data);

        return $this;
    }

    public function setHeader(string $key, string $value): HttpResponse {
        if (strtolower($key) == "content-type") {
            static::$contentType = $value;
        }

        header("$key: $value");
        return $this;
    }

    public static function getContentType(): string {
        return static::$contentType ?? '';
    }
}
