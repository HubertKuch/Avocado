<?php

namespace Avocado\DataSource\Exceptions;

use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\HTTP\HTTPStatus;
use Exception;

#[ResponseStatus(HTTPStatus::INTERNAL_SERVER_ERROR)]
class CannotBuildDataSourceException extends Exception {


    public function __construct(string $msg) {
        parent::__construct($msg);
    }
}