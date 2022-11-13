<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Attributes\Request\RequestHeader;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestHeaderParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, AvocadoRequest $request, AvocadoResponse $response): mixed {
        if (RequestHeader::isAnnotated($parameterRef) && $parameterRef->getType()->getName() === "string") {
            $instanceOfAnnotation = RequestHeader::getInstance($parameterRef);
            $name = $instanceOfAnnotation->getName();
            $value = null;

            if ($request->hasHeader($name)) {
                $value = $request->headers[$name];
            }

            return $value;
        }

        return CannotBeProcessed::of();
    }
}