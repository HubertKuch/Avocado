<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\HTTP\HTTPStatus;
use PHPUnit\Framework\Exception;

#[ResponseStatus(HTTPStatus::CONFLICT)]
class MockedExceptionWithResponseStatus extends Exception {}
