<?php

namespace Avocado\AvocadoApplication;

use Exception;
use Avocado\HTTP\ResponseBody;
use Avocado\Utils\ReflectionUtils;
use Avocado\Router\HttpResponse;
use Avocado\AvocadoApplication\DependencyInjection\Resourceable;
use Avocado\AvocadoApplication\Attributes\Exceptions\ResponseStatus;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use AvocadoApplication\DependencyInjection\DependencyInjectionService;
use Avocado\AvocadoApplication\ExceptionHandlerLogic\ExceptionHandlerStrategy;
use Avocado\AvocadoApplication\ExceptionHandlerLogic\InvokeExceptionHandlerStrategy;
use Avocado\AvocadoApplication\ExceptionHandlerLogic\ExceptionResponseStatusStrategy;
use Avocado\AvocadoApplication\ExceptionHandlerLogic\StandardExceptionHandlerStrategy;
use Throwable;

class ApplicationExceptionsAdvisor {

    /** @var ExceptionHandler[] $handlers*/
    private static array $handlers;

    public static function process(Throwable $exception): void {
        self::getExceptionHandlers();

        $exceptionHandlerStrategy = self::getExceptionStrategy($exception);

        $response = $exceptionHandlerStrategy->handle($exception, self::getHandlerForThrowable($exception));

        self::throwResponse($response);
    }

    private static function throwResponse(ResponseBody $responseBody): void {
        $response = new HttpResponse();

        $response
            -> json($responseBody->getData())
            -> withStatus($responseBody->getStatus());
    }

    private static function getExceptionHandlers(): void {
        $resources = DependencyInjectionService::getResources();
        $handlers = [];

        foreach ($resources as $resource) {
            $resource->getTargetInstance();
            $exceptionHandlerAttribute = ReflectionUtils::getAttributeFromClass($resource->getMainType(), ExceptionHandler::class);

            if (!$exceptionHandlerAttribute) continue;

            array_push($handlers, ...self::getHandlersFromExceptionHandlerClass($resource));
        }

        self::$handlers = $handlers;
    }

    /** @return ExceptionHandler[] */
    private static function getHandlersFromExceptionHandlerClass(Resourceable $resource): array {
        $handlers = ReflectionUtils::getMethods($resource->getMainType(), ExceptionHandler::class);

        return array_map(
            function($method) use ($resource) {
                return new ExceptionHandler(
                    ReflectionUtils::getAttributeFromMethod($method->getDeclaringClass()->getName(), $method->getName(), ExceptionHandler::class)
                        -> newInstance()
                        -> getExceptions(),
                    $resource->getMainType(),
                    $method->getName()
                );
            }, $handlers
        );
    }

    private static function getExceptionStrategy(Throwable $throwable): ExceptionHandlerStrategy {
        $strategy = new StandardExceptionHandlerStrategy();

        $handler = self::getHandlerForThrowable($throwable);
        $responseStatusAttribute = ReflectionUtils::getAttributeFromClass(get_class($throwable), ResponseStatus::class);

        if (!$handler && $responseStatusAttribute)
            $strategy = new ExceptionResponseStatusStrategy();
        else if ($handler && !$responseStatusAttribute)
            $strategy = new InvokeExceptionHandlerStrategy();

        return $strategy;
    }

    /**
     * @param Throwable $throwable
     * @return ExceptionHandler|null
     */
    private static function getHandlerForThrowable(Throwable $throwable): ExceptionHandler|null {
        $handlers = self::$handlers;
        $handler = null;

        foreach ($handlers as $item) {
            if($item->isMatchException(get_class($throwable))) {
                $handler = $item;
                break;
            }
        }

        return $handler;
    }
}
