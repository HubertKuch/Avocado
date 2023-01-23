<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, HttpRequest $request, HttpResponse $response): mixed {
        if ($parameterRef->getType()->getName() === $request::class) {
            return $request;
        }

        return CannotBeProcessed::of();
    }
}