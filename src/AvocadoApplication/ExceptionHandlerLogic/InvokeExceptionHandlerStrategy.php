<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Avocado\AvocadoApplication\Exceptions\InvalidResourceException;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use Exception;
use ReflectionException;
use ReflectionMethod;

class InvokeExceptionHandlerStrategy implements ExceptionHandlerStrategy {

    public function handle(Exception $exception, ?ExceptionHandler $handler = null): ResponseBody {
        try {
            $ref = new ReflectionMethod($handler->getClassname(), $handler->getMethodName());

            $resource = DependencyInjectionService::getResourceByType($handler->getClassname())->getTargetInstance();

            $returns = $ref->invoke($resource);

            if (!($returns instanceof ResponseBody)) {
                throw new InvalidResourceException("Exception handler method must returns `ResponseBody` instance.");
            }

            return $returns;
        } catch (ReflectionException $e) {
            return new ResponseBody([], HTTPStatus::INTERNAL_SERVER_ERROR);
        }
    }

}
