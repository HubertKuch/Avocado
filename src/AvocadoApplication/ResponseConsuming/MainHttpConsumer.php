<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\ContentType;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\Managers\HttpConsumer;
use Avocado\HTTP\Managers\HttpConsumingStrategy;
use Avocado\HTTP\ResponseBody;
use Avocado\Router\HttpResponse;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\Resource;

#[Resource]
class MainHttpConsumer implements HttpConsumer {

    #[Autowired(autowiredResourceName: "responseBodyStrategy")]
    private readonly HttpConsumingStrategy $responseBodyStrategy;

    #[Autowired(autowiredResourceName: "standardResponseStrategy")]
    private readonly HttpConsumingStrategy $standardResponseStrategy;

    #[Autowired(autowiredResourceName: "parsingDataStrategy")]
    private readonly HttpConsumingStrategy $parsingDataStrategy;

    public function __construct() {}

    public function consume(ResponseBody $responseBody) {
        if ($responseBody->getData() instanceof ResponseBody) {
            $this->responseBodyStrategy->consume($responseBody);
        } else if ($responseBody->getData() instanceof HttpResponse) {
            $this->standardResponseStrategy->consume($responseBody);
        } else {
            $this->parsingDataStrategy->consume($responseBody);
        }
    }
}