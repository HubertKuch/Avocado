<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Attributes\Request\RequestQuery;
use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\Exceptions\MissingRequestQueryException;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\Optional;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestQueryParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, HttpRequest $request, HttpResponse $response): mixed {

        if (AnnotationUtils::isAnnotated($parameterRef, RequestQuery::class)) {
            $instanceOfAnnotation = AnnotationUtils::getInstance($parameterRef, RequestQuery::class);
            $name = $instanceOfAnnotation->getName();

            if (!array_key_exists($name, $request->query) && $instanceOfAnnotation->isRequired()) {
                if ($parameterRef->getType()->getName() === Optional::class) {
                    return Optional::empty();
                }

                throw new MissingRequestQueryException(sprintf("Missing `%s` query param.", $name));
            }

            $value = $request->query[$name] ?? ($instanceOfAnnotation->getDefaultValue() ?? null);

            if ($parameterRef->getType()->getName() === Optional::class) {
                return Optional::of($value);
            }

            return $value;
        }

        return CannotBeProcessed::of();
    }
}