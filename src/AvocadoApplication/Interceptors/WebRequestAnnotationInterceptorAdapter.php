<?php

namespace Avocado\AvocadoApplication\Interceptors;

use Avocado\AvocadoApplication\Interceptors\Utils\WebRequestHandler;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use ReflectionObject;

interface WebRequestAnnotationInterceptorAdapter {

    function preHandle(HttpRequest $request, HttpResponse $response, WebRequestHandler $handler): bool;

}