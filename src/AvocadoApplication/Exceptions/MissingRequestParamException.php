<?php

namespace Avocado\AvocadoApplication\Exceptions;

use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\HTTP\HTTPStatus;
use Exception;

#[ResponseStatus(HTTPStatus::BAD_REQUEST)]
class MissingRequestParamException extends Exception {}