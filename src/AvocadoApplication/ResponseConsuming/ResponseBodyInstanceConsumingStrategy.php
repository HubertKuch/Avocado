<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\Managers\HttpConsumingStrategy;
use Avocado\HTTP\ResponseBody;
use Avocado\Router\AvocadoResponse;
use AvocadoApplication\Attributes\Resource;

#[Resource(name: "responseBodyStrategy")]
class ResponseBodyInstanceConsumingStrategy implements HttpConsumingStrategy {

    public function __construct() {
    }

    /**
     * @param ResponseBody $data
     * */
    function consume(mixed $data, HTTPStatus $status): void {
        (new AvocadoResponse())
            ->withStatus($data->getStatus())
            ->json($data->getData());
    }

}