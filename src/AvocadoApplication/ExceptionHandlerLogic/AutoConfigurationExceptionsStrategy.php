<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Exception;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;

class AutoConfigurationExceptionsStrategy implements ExceptionHandlerStrategy {

    public function handle(Exception $exception, ?ExceptionHandler $handler = null): ResponseBody {
        return new ResponseBody(["test" => "TEST"], HTTPStatus::BAD_REQUEST);
    }
}
