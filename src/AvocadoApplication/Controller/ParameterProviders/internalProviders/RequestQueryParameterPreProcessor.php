<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\Exceptions\MissingRequestQueryException;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Tests\Unit\Application\RequestQuery;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestQueryParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, AvocadoRequest $request, AvocadoResponse $response): mixed {

        if (RequestQuery::isAnnotated($parameterRef)) {
            $instanceOfAnnotation = RequestQuery::getInstance($parameterRef);
            $name = $instanceOfAnnotation->getName();

            if (!array_key_exists($name, $request->query) && $instanceOfAnnotation->isRequired()) {
                throw new MissingRequestQueryException(sprintf("Missing `%s` query param.", $name));
            }

            return $request->query[$name] ?? ($instanceOfAnnotation->getDefaultValue() ?? null);
        }

        return CannotBeProcessed::of();
    }
}