<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\Managers\HttpConsumingStrategy;
use Avocado\Router\AvocadoResponse;
use AvocadoApplication\Attributes\Resource;

#[Resource(name: "parsingDataStrategy")]
class ParsingDataConsumingStrategy implements HttpConsumingStrategy {

    public function __construct() {
    }

    function consume(mixed $data, HTTPStatus $status): void {
        (new AvocadoResponse())
            ->json($data)
            ->withStatus($status);
    }
}