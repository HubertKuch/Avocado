<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders;

use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use ReflectionMethod;
use ReflectionParameter;

interface SpecificParametersPreProcessor {
    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, HttpRequest $request, HttpResponse $response): mixed;
}