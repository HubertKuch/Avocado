<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders;

use Avocado\AvocadoApplication\Attributes\Request\RequestBody;
use Avocado\AvocadoApplication\Attributes\Request\RequestHeader;
use Avocado\AvocadoApplication\Exceptions\InvalidRequestBodyException;
use Avocado\AvocadoApplication\Exceptions\MissingKeyException;
use Avocado\AvocadoApplication\Exceptions\MissingRequestParamException;
use Avocado\AvocadoApplication\Exceptions\MissingRequestQueryException;
use Avocado\AvocadoApplication\PreProcessors\CannotBeProcessed;
use Avocado\AvocadoApplication\PreProcessors\PreProcessorsManager;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use Avocado\Tests\Unit\Application\RequestParam;
use Avocado\Tests\Unit\Application\RequestQuery;
use Avocado\Utils\StandardObjectMapper;
use ReflectionException;
use ReflectionMethod;

class ControllerParametersProcessor {

    /**
     * @return mixed[] Returns array of method parameters like [4, $request, $user, $route]
     * */
    public static function process(ReflectionMethod $methodRef, HttpRequest $request, HttpResponse $response): array {
        $preProcessors = PreProcessorsManager::getControllerPreProcessors();
        $methodParameters = $methodRef->getParameters();
        $parametersToProvide = [];

        foreach ($methodParameters as $parameter) {
            $parameterRef = new \ReflectionParameter(array($methodRef->class, $methodRef->getName()), $parameter->getName());

            $processedOutput = array_map(function($processor) use ($methodRef, $request, $response, $parameterRef) {
                return $processor->process($methodRef, $parameterRef, $request, $response);
            }, $preProcessors);

            $validValues = array_filter($processedOutput, fn($output) => !($output instanceof CannotBeProcessed));

            if (empty($validValues)) {
                $parametersToProvide[] = null;
                continue;
            }

            $parametersToProvide[] = $validValues[key($validValues)];
        }

        return $parametersToProvide;
    }

}