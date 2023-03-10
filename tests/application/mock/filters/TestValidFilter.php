<?php

namespace Avocado\Tests\Unit;

use Avocado\AvocadoApplication\Filters\Filter;
use Avocado\AvocadoApplication\Filters\RequestFilter;
use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;
use AvocadoApplication\Attributes\Resource;

#[Filter]
#[Resource]
class TestValidFilter implements RequestFilter {
    public function __construct() {}

    public function filter(HttpRequest $request, HttpResponse $response): bool {
        return true;
    }
}