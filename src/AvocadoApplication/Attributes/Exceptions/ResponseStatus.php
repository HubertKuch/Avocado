<?php

namespace Avocado\AvocadoApplication\Attributes\Exceptions;

use Attribute;
use Avocado\HTTP\HTTPStatus;

#[Attribute]
class ResponseStatus {
    private HTTPStatus $status;

    public function __construct(HTTPStatus $status) {
        $this->status = $status;
    }

    public function getStatus(): HTTPStatus {
        return $this->status;
    }
}
