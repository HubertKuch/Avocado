<?php

namespace Avocado\HTTP;

class ResponseBody {
    public function __construct(
        private readonly mixed $data,
        private readonly HTTPStatus $status,
        private readonly ContentType $contentType = ContentType::APPLICATION_JSON
    ) {}

    public function getData(): mixed {
        return $this->data;
    }

    public function getStatus(): HTTPStatus {
        return $this->status;
    }

    public function getContentType(): ContentType {
        return $this->contentType;
    }
}
