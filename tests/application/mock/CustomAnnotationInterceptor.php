<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Interceptors\Utils\WebRequestHandler;
use Avocado\AvocadoApplication\Interceptors\WebRequestAnnotationInterceptorAdapter;
use Avocado\AvocadoApplication\PreProcessors\PreProcessor;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use AvocadoApplication\Attributes\Resource;
use Exception;

#[Resource]
#[PreProcessor]
class CustomAnnotationInterceptor implements WebRequestAnnotationInterceptorAdapter {
    public function __construct() {}

    /**
     * @throws Exception
     */
    function preHandle(HttpRequest $request, HttpResponse $response, WebRequestHandler $handler): bool {
        if ($handler->hasAnnotation(CustomAnnotation::class)) {
            throw new Exception("Hello from interceptor");
        }

        return true;
    }
}