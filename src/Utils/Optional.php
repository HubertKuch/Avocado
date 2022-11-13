<?php

namespace Avocado\Utils;

class Optional {

    public function __construct(private mixed $data) {}

    public static function of(mixed $data) {
        return new Optional($data);
    }

    public static function empty() {
        return new Optional(null);
    }

    public function isPresent(): bool {
        return $this->data !== null;
    }

    public function isEmpty(): bool {
        return !$this->isPresent();
    }

    public function orElseGet(mixed $data): mixed {
        if ($this->isEmpty()) {
            return $data;
        }

        return $this->get();
    }

    public function orElseThrow(callable $exceptionSupplier): mixed {
        if ($this->isEmpty()) {
            $exceptionSupplier();
        }

        return $this->get();
    }

    public function orElseDo(callable $action): void {
        if ($this->isEmpty()) {
            $action();
        }
    }

    public function get(): mixed {
        return $this->data;
    }
}
