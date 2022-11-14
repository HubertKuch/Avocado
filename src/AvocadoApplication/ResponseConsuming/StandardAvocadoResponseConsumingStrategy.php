<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\Managers\HttpConsumingStrategy;
use AvocadoApplication\Attributes\Resource;

#[Resource(name: "standardResponseStrategy")]
class StandardAvocadoResponseConsumingStrategy implements HttpConsumingStrategy {

    public function __construct() {
    }

    function consume(mixed $data, HTTPStatus $status): void {}
}