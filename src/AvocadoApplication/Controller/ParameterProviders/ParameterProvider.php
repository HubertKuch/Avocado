<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders;

use Avocado\AvocadoApplication\Attributes\RequestBody;
use Avocado\AvocadoApplication\Exceptions\InvalidRequestBodyException;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\StandardObjectMapper;
use ReflectionException;
use ReflectionMethod;

class ParameterProvider {


    /**
     * @return mixed[] Returns array of method parameters like [4, $request, $user, $route]
     * */
    public static function provide(ReflectionMethod $methodRef, AvocadoRequest $request, AvocadoResponse $response): array {
        $methodParameters = $methodRef->getParameters();
        $parametersToProvide = [];

        foreach ($methodParameters as $parameter) {
            $parameterRef = new \ReflectionParameter(array($methodRef->class, $methodRef->getName()), $parameter->getName());

            if (RequestBody::isAnnotated($parameterRef)) {
                try {
                    $instanceOf = StandardObjectMapper::arrayToObject($request->body, $parameterRef->getType()->getName());
                } catch (MissingKeyException|ReflectionException) {
                    throw new InvalidRequestBodyException("Invalid request body.");
                }

                $parametersToProvide[] = $instanceOf;
                continue;
            }

            if ($parameterRef->getType()->getName() === $request::class) {
                $parametersToProvide[] = $request;
                continue;
            }

            if ($parameterRef->getType()->getName() === $response::class) {
                $parametersToProvide[] = $response;
                continue;
            }

            $parametersToProvide[] = null;
        }

        return $parametersToProvide;
    }

}