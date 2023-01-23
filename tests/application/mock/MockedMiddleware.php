<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Middleware\Next;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use Exception;

class MockedMiddleware {

    public static function test(HttpRequest $request, HttpResponse $response, Next $next): ?Next {
        throw new Exception();
    }

}