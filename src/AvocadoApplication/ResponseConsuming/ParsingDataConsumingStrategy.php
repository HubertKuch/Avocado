<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\ContentType;
use Avocado\HTTP\Managers\HttpConsumingStrategy;
use Avocado\HTTP\ResponseBody;
use Avocado\Router\AvocadoResponse;
use Avocado\Utils\Json\JsonSerializer;
use AvocadoApplication\Attributes\Resource;
use stdClass;

#[Resource(name: "parsingDataStrategy")]
class ParsingDataConsumingStrategy implements HttpConsumingStrategy {

    public function __construct() {
    }

    function consume(ResponseBody $responseBody): void {
        if ($responseBody->getContentType() === ContentType::APPLICATION_JSON) {
            $parsedData = JsonSerializer::serialize($responseBody->getData());

            (new AvocadoResponse())
                ->setHeader("Content-Type", ContentType::APPLICATION_JSON->value)
                ->write($parsedData)
                ->withStatus($responseBody->getStatus());

            return;
        }

        (new AvocadoResponse())
            ->setHeader("Content-Type", $responseBody->getContentType()->value)
            ->write($responseBody->getData())
            ->withStatus($responseBody->getStatus());
    }
}