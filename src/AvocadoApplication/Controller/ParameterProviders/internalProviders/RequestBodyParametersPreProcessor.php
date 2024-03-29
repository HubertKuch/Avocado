<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;


use Avocado\AvocadoApplication\Attributes\Request\RequestBody;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\Exceptions\InvalidRequestBodyException;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\Optional;
use Avocado\Utils\StandardObjectMapper;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestBodyParametersPreProcessor implements SpecificParametersPreProcessor {

    /**
     * @throws InvalidRequestBodyException
     */
    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, HttpRequest $request, HttpResponse $response): mixed {
        if (AnnotationUtils::isAnnotated($parameterRef, RequestBody::class)) {
            try {
                $annotationInstance = AnnotationUtils::getInstance($parameterRef, RequestBody::class);
                $type = $annotationInstance->getType() ?? $parameterRef->getType()->getName();

                $instanceOf = StandardObjectMapper::arrayToObject($request->body, $type);
            } catch (MissingKeyException|ReflectionException $e) {
                if ($parameterRef->getType()->getName() === Optional::class) {
                    return Optional::empty();
                }

                throw new InvalidRequestBodyException("Invalid request body.");
            }

            return $instanceOf;
        }

        return CannotBeProcessed::of();
    }
}