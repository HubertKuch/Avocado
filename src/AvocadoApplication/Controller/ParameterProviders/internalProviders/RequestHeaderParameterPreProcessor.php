<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Attributes\Request\RequestHeader;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\AnnotationUtils;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestHeaderParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, AvocadoRequest $request, AvocadoResponse $response): mixed {
        if (AnnotationUtils::isAnnotated($parameterRef, RequestHeader::class) && $parameterRef->getType()->getName() === "string") {
            $instanceOfAnnotation = AnnotationUtils::getInstance($parameterRef, RequestHeader::class);
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