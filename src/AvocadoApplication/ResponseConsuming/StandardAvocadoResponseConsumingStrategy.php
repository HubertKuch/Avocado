<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\Managers\HttpConsumingStrategy;
use Avocado\HTTP\ResponseBody;
use AvocadoApplication\Attributes\Resource;

#[Resource(name: "standardResponseStrategy")]
class StandardAvocadoResponseConsumingStrategy implements HttpConsumingStrategy {

    public function __construct() {
    }

    function consume(ResponseBody $responseBody): void {}
}