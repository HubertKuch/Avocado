<?php

namespace Avocado\AvocadoApplication;

use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;
use Avocado\AvocadoApplication\ExceptionHandlerLogic\ExceptionHandlerStrategy;
use Avocado\AvocadoApplication\ExceptionHandlerLogic\ExceptionResponseStatusStrategy;
use Avocado\AvocadoApplication\ExceptionHandlerLogic\InvokeExceptionHandlerStrategy;
use Avocado\AvocadoApplication\ExceptionHandlerLogic\StandardExceptionHandlerStrategy;
use Avocado\HTTP\ResponseBody;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\ReflectionUtils;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use Exception;

class ApplicationExceptionsAdvisor {

    /** @var ExceptionHandler[] $handlers*/
    private static array $handlers;

    public static function process(Exception $exception): void {
        self::getExceptionHandlers();

        $exceptionHandlerStrategy = self::getExceptionStrategy($exception);

        $response = $exceptionHandlerStrategy->handle($exception, self::getHandlerForException($exception));

        self::throwResponse($response);
    }

    private static function throwResponse(ResponseBody $responseBody): void {
        $response = new AvocadoResponse();

        $response
            -> json($responseBody->getData())
            -> withStatus($responseBody->getStatus());
    }

    private static function getExceptionHandlers() {
        $resources = DependencyInjectionService::getResources();
        $handlers = [];

        foreach ($resources as $resource) {
            $resource->getTargetInstance();
            $exceptionHandlerAttribute = ReflectionUtils::getAttributeFromClass($resource->getTargetResourceClass(), ExceptionHandler::class);

            if (!$exceptionHandlerAttribute) continue;

            array_push($handlers, ...self::getHandlersFromExceptionHandlerClass($resource));
        }

        self::$handlers = $handlers;
    }

    /** @return ExceptionHandler[] */
    private static function getHandlersFromExceptionHandlerClass(Resourceable $resource): array {
        $handlers = ReflectionUtils::getMethods($resource->getTargetResourceClass(), ExceptionHandler::class);

        return array_map(
            function($method) use ($resource) {
                return new ExceptionHandler(
                    ReflectionUtils::getAttributeFromMethod($method->getDeclaringClass()->getName(), $method->getName(), ExceptionHandler::class)
                        -> newInstance()
                        -> getExceptions(),
                    $resource->getTargetResourceClass(),
                    $method->getName()
                );
            }, $handlers
        );
    }

    private static function getExceptionStrategy(Exception $exception): ExceptionHandlerStrategy {
        $strategy = new StandardExceptionHandlerStrategy();

        $handler = self::getHandlerForException($exception);
        $responseStatusAttribute = ReflectionUtils::getAttributeFromClass(get_class($exception), ResponseStatus::class);

        if (!$handler && $responseStatusAttribute)
            $strategy = new ExceptionResponseStatusStrategy();
        else if ($handler && !$responseStatusAttribute)
            $strategy = new InvokeExceptionHandlerStrategy();

        return $strategy;
    }

    /**
     * @param Exception $exception
     * @return ExceptionHandler|null
     */
    private static function getHandlerForException(Exception $exception): ExceptionHandler|null {
        $handlers = self::$handlers;
        $handler = null;

        foreach ($handlers as $item) {
            if($item->isMatchException(get_class($exception))) {
                $handler = $item;
                break;
            }
        }

        return $handler;
    }
}
