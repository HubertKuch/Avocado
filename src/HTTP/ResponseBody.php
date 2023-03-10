<?php

namespace Avocado\HTTP;

/**
 * @template T
 * */
class ResponseBody {
    /**
     * @param class-string<T> $targetClass
     * */
    public function __construct(
        private readonly mixed $data,
        private readonly HTTPStatus $status,
        private readonly ContentType $contentType = ContentType::APPLICATION_JSON,
        private readonly ?string $targetClass = null
    ) {}

    /**
     * @returns T
     * */
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
