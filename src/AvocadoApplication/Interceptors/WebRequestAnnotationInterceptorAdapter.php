<?php

namespace Avocado\AvocadoApplication\Interceptors;

use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use ReflectionObject;

interface WebRequestAnnotationInterceptorAdapter {

    function preHandle(HttpRequest $request, HttpResponse $response, ReflectionObject $handler);

}