<?php

namespace Avocado\AvocadoApplication\Controller\ParameterProviders;

use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;
use ReflectionMethod;
use ReflectionParameter;

interface SpecificParametersPreProcessor {
    public function process(ReflectionMethod $methodRef, ReflectionParameter $parameterRef, AvocadoRequest $request, AvocadoResponse $response): mixed;
}