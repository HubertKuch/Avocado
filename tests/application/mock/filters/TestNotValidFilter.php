<?php

namespace Avocado\Tests\Unit;

use _PHPStan_4dd92cd93\Nette\Neon\Exception;
use Avocado\AvocadoApplication\Filters\Filter;
use Avocado\AvocadoApplication\Filters\RequestFilter;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use AvocadoApplication\Attributes\Resource;

#[Filter]
#[Resource]
class TestNotValidFilter implements RequestFilter {
    public function __construct() {}

    public function filter(HttpRequest $request, HttpResponse $response): bool {
        return false;
    }
}