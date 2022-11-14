<?php

namespace Avocado\AvocadoApplication\Middleware;

use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\AnnotationUtils;
use ReflectionMethod;

#[PreProcessor]
class MiddlewareProcessor {

    public function validRequest(ReflectionMethod $method, AvocadoRequest $request, AvocadoResponse $response): ?Next {
        $class = $method->getDeclaringClass();
        $middlewareStack = [];

        if (AnnotationUtils::isAnnotated($class, BeforeRoute::class)) {
            $beforeRoutes = AnnotationUtils::getInstance($class, BeforeRoute::class);

            array_push($middlewareStack, ...$beforeRoutes->getCallbacks());
        }

        if (AnnotationUtils::isAnnotated($method, BeforeRoute::class)) {
            $beforeRouteInstance = AnnotationUtils::getInstance($method, BeforeRoute::class);
            array_push($middlewareStack, ...$beforeRouteInstance->getCallbacks());
        }

        $previousReturnedData = new Next();

        foreach ($middlewareStack as $callback) {
            $returnedData = call_user_func_array($callback, [$request, $response, $previousReturnedData]);

            if (!$returnedData) {
                $previousReturnedData = null;
                break;
            }

            $previousReturnedData = $returnedData;
        }

        return $previousReturnedData;
    }

}