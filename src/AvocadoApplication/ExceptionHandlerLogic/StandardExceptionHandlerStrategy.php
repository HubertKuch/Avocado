<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Avocado\HTTP\ResponseBody;
use Exception;

class StandardExceptionHandlerStrategy implements ExceptionHandlerStrategy {

    /**
     * @throws Exception
     */
    public function handle(Exception $exception, ?ExceptionHandler $handler = null): ResponseBody {
        throw $exception;
    }
}
