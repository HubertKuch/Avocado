<?php

namespace Avocado\Router;

use Avocado\AvocadoRouter\HTTPStatus;

class AvocadoResponse {
    public function write($data): AvocadoResponse {
        if (is_array($data) || is_object($data)) {
            echo "<pre>";
            var_dump($data);
            echo "</pre>";

            return $this;
        }

        echo $data;

        return $this;
    }

    public function withStatus(HTTPStatus|int $status): AvocadoResponse{
        http_response_code($status instanceof HTTPStatus ? $status->value : $status);

        return $this;
    }

    public function withCookie(string $key, string $value, ?array $options = array()): AvocadoResponse {
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

    public function json(array $data): AvocadoResponse {
        header('Content-Type: application/json; charset=utf-8');
        print(json_encode($data));

        return $this;
    }

    public function setHeader(string $key, string $value): AvocadoResponse {
        header("$key: $value");
        return $this;
    }
}
