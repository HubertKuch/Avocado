<?php

namespace Avocado\AvocadoApplication\Files\Exceptions;

use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\HTTP\HTTPStatus;
use Exception;
use Throwable;

#[ResponseStatus(HTTPStatus::INTERNAL_SERVER_ERROR)]
class CannotMoveFileException extends Exception {

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}