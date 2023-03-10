<?php

namespace Avocado\AvocadoApplication\Filters;

use Avocado\Router\HttpRequest;
use Avocado\Router\HttpResponse;

interface RequestFilter {
    public function filter(HttpRequest $request, HttpResponse $response): bool;
}