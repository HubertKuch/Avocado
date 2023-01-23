<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Attributes\Request\RequestHeader;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\Optional;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestHeaderParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, HttpRequest $request, HttpResponse $response): mixed {
        if (AnnotationUtils::isAnnotated($parameterRef, RequestHeader::class)) {
            $instanceOfAnnotation = AnnotationUtils::getInstance($parameterRef, RequestHeader::class);
            $name = $instanceOfAnnotation->getName();
            $value = null;

            if ($request->hasHeader($name)) {
                $value = $request->headers[$name];
            }

            if ($parameterRef->getType()->getName() === Optional::class) {
                return Optional::of($value);
            }


            if ($value === null) return Optional::empty();

            return $value;
        }

        return CannotBeProcessed::of();
    }
}