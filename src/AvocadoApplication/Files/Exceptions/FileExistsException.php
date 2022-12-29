<?php

namespace Avocado\AvocadoApplication\Files\Exceptions;

use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\HTTP\HTTPStatus;
use Exception;

#[ResponseStatus(HTTPStatus::BAD_REQUEST)]
class FileExistsException extends Exception {

    public function __construct(string $msg) {
        parent::__construct($msg);
    }

}