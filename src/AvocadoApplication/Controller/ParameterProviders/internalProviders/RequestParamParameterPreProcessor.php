<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\Exceptions\MissingRequestParamException;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use Avocado\Tests\Unit\Application\RequestParam;
use Avocado\Utils\AnnotationUtils;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestParamParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, AvocadoRequest $request, AvocadoResponse $response): mixed {
        if (AnnotationUtils::isAnnotated($parameterRef, RequestParam::class)) {
            $instanceOfAnnotation = AnnotationUtils::getInstance($parameterRef, RequestParam::class);
            $nameOfParam = $instanceOfAnnotation->getName();

            if (!$request->hasParam($nameOfParam) && $instanceOfAnnotation->isRequired()) {
                throw new MissingRequestParamException(sprintf("Missing `%s` param.", $nameOfParam));
            }

            $value = $request->params[$nameOfParam] ?? ($instanceOfAnnotation->getDefaultValue() ?? null);

            return $value;
        }

        return CannotBeProcessed::of();
    }
}