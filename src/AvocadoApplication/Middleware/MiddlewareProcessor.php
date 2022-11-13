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
        if (!AnnotationUtils::isAnnotated($method, BeforeRoute::class)) {
            return new Next();
        }

        $beforeRouteInstance = AnnotationUtils::getInstance($method, BeforeRoute::class);
        $callbacks = $beforeRouteInstance->getCallbacks();
        $previousReturnedData = new Next();

        foreach ($callbacks as $callback) {
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