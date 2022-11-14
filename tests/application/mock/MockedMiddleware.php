<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Middleware\Next;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;

class MockedMiddleware {

    public static function test(AvocadoRequest $request, AvocadoResponse $response, Next $next): ?Next {
    }

}