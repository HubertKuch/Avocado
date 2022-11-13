<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;


use Avocado\AvocadoApplication\Attributes\Request\RequestBody;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\Exceptions\InvalidRequestBodyException;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\StandardObjectMapper;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestBodyParametersPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, AvocadoRequest $request, AvocadoResponse $response): mixed {
        if (AnnotationUtils::isAnnotated($parameterRef, RequestBody::class)) {
            try {
                $instanceOf = StandardObjectMapper::arrayToObject($request->body, $parameterRef->getType()->getName());
            } catch (MissingKeyException|ReflectionException) {
                throw new InvalidRequestBodyException("Invalid request body.");
            }

            return $instanceOf;
        }

        return CannotBeProcessed::of();
    }
}