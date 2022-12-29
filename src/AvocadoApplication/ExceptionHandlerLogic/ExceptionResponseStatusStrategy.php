<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Exception;
use Avocado\HTTP\ResponseBody;
use Avocado\Utils\ReflectionUtils;
use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;

class ExceptionResponseStatusStrategy implements ExceptionHandlerStrategy {

    public function handle(Exception $exception, ?ExceptionHandler $handler = null): ResponseBody {
        $responseStatusAttr = ReflectionUtils::getAttribute($exception, ResponseStatus::class);

        $responseStatusInstance = $responseStatusAttr->newInstance();
        $message = $exception->getMessage();

        $statusCode = $responseStatusInstance->getStatus();

        $body = ["message" => $message, "status" => $statusCode->value, "path" => str_replace("Standard input code", "", $_SERVER['PHP_SELF'])];

        if ($exception->getPrevious()) {
            $body['cause'] = $exception->getPrevious()->getMessage();
        }

        return new ResponseBody($body, $statusCode);
    }
}
