<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\HTTP\HTTPStatus;
use PHPUnit\Framework\Exception;
use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;

#[ResponseStatus(HTTPStatus::CONFLICT)]
class MockedExceptionWithResponseStatus extends Exception {}
