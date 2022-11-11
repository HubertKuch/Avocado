<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use AvocadoApplication\Attributes\Resource;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;

#[Resource]
#[ExceptionHandler]
class MockedExceptionHandler {

    public function __construct() {}

    #[ExceptionHandler([MockedException::class])]
    public function handleMocked(): ResponseBody {

        return new ResponseBody([
            "status" => HTTPStatus::BAD_REQUEST->value,
            "message" => "test",
        ], HTTPStatus::BAD_REQUEST);
    }
}
