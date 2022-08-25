<?php

namespace Avocado\HTTP;

class ResponseBody {
    public function __construct(
        private mixed $data,
        private HTTPStatus $status
    ) {}

    public function getData(): mixed {
        return $this->data;
    }

    public function getStatus(): HTTPStatus {
        return $this->status;
    }
}
