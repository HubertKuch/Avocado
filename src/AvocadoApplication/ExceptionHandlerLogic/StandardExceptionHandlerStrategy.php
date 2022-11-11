<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Exception;
use Avocado\HTTP\ResponseBody;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;

class StandardExceptionHandlerStrategy implements ExceptionHandlerStrategy {

    /**
     * @throws Exception
     */
    public function handle(Exception $exception, ?ExceptionHandler $handler = null): ResponseBody {
        throw $exception;
    }
}
