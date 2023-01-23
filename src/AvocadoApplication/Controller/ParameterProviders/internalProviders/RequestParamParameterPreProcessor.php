<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders\internalProviders;

use Avocado\AvocadoApplication\Controller\ParameterProviders\SpecificParametersPreProcessor;
use Avocado\AvocadoApplication\Exceptions\MissingRequestParamException;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use Avocado\Tests\Unit\Application\RequestParam;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\Optional;
use ReflectionMethod;
use ReflectionParameter;

#[PreProcessor]
class RequestParamParameterPreProcessor implements SpecificParametersPreProcessor {

    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, HttpRequest $request, HttpResponse $response): mixed {
        if (AnnotationUtils::isAnnotated($parameterRef, RequestParam::class)) {
            $instanceOfAnnotation = AnnotationUtils::getInstance($parameterRef, RequestParam::class);
            $nameOfParam = $instanceOfAnnotation->getName();

            if (!$request->hasParam($nameOfParam) && $instanceOfAnnotation->isRequired()) {
                if ($parameterRef->getType()->getName() === Optional::class) {
                    return Optional::empty();
                }

                throw new MissingRequestParamException(sprintf("Missing `%s` param.", $nameOfParam));
            }

            $value = $request->params[$nameOfParam] ?? ($instanceOfAnnotation->getDefaultValue() ?? null);

            if ($parameterRef->getType()->getName() === Optional::class) {
                return Optional::of($value);
            }

            return $value;
        }

        return CannotBeProcessed::of();
    }
}