<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Exception;
use ReflectionMethod;
use ReflectionException;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;
use Avocado\AvocadoApplication\Exceptions\InvalidResourceException;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Throwable;

class InvokeExceptionHandlerStrategy implements ExceptionHandlerStrategy {

    /**
     * @throws InvalidResourceException
     */
    public function handle(Throwable $throwable, ?ExceptionHandler $handler = null): ResponseBody {
        try {
            $ref = new ReflectionMethod($handler->getClassname(), $handler->getMethodName());

            $resource = DependencyInjectionService::getResourceByType($handler->getClassname())->getTargetInstance();

            $returns = $ref->invoke($resource);

            if (!($returns instanceof ResponseBody)) {
                throw new InvalidResourceException("Exception handler method must returns `ResponseBody` instance.");
            }

            return $returns;
        } catch (ReflectionException $ignored) {
            return new ResponseBody([], HTTPStatus::INTERNAL_SERVER_ERROR);
        }
    }

}
