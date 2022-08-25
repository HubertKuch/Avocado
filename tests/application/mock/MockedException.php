<?php

namespace Avocado\Tests\Unit\Application;


use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\HTTP\HTTPStatus;
use Exception;

#[ResponseStatus(HTTPStatus::BAD_REQUEST)]
class MockedException extends Exception {}
