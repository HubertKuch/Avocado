<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\HTTP\ResponseBody;
use Avocado\Utils\ReflectionUtils;
use Exception;

class ExceptionResponseStatusStrategy implements ExceptionHandlerStrategy {

    public function handle(Exception $exception, ?ExceptionHandler $handler = null): ResponseBody {
        $responseStatusAttr = ReflectionUtils::getAttribute($exception, ResponseStatus::class);

        $responseStatusInstance = $responseStatusAttr->newInstance();
        $message = $exception->getMessage();

        $statusCode = $responseStatusInstance->getStatus();

        return new ResponseBody([
            "message" => $message,
            "status" => $statusCode->value
        ], $statusCode);
    }
}
