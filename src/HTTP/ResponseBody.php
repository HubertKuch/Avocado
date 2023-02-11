<?php

namespace Avocado\HTTP;

class ResponseBody {
    public function __construct(
        private readonly mixed $data,
        private readonly HTTPStatus $status,
        private readonly ContentType $contentType = ContentType::APPLICATION_JSON,
        private readonly ?string $targetClass = null
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

    public function getTargetClass(): ?string {
        return $this->targetClass;
    }
}
