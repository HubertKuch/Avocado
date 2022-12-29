<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\ContentType;
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
    function consume(ResponseBody $responseBody): void {
        if ($responseBody->getContentType() === ContentType::APPLICATION_JSON) {
            (new AvocadoResponse())
                ->json($responseBody->getData()->getData())
                ->withStatus($responseBody->getStatus());

            return;
        }

        (new AvocadoResponse())
            ->setHeader("Content-Type", $responseBody->getContentType()->value)
            ->withStatus($responseBody->getStatus())
            ->write($responseBody->getData());
    }

}