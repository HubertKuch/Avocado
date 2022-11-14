<?php

namespace Avocado\AvocadoApplication\ResponseConsuming;

use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\Managers\HttpConsumer;
use Avocado\HTTP\Managers\HttpConsumingStrategy;
use Avocado\HTTP\ResponseBody;
use Avocado\Router\AvocadoResponse;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\Resource;

#[Resource]
class MainHttpConsumer implements HttpConsumer {

    #[Autowired(autowiredResourceName: "responseBodyStrategy")]
    private HttpConsumingStrategy $responseBodyStrategy;

    #[Autowired(autowiredResourceName: "standardResponseStrategy")]
    private HttpConsumingStrategy $standardResponseStrategy;

    #[Autowired(autowiredResourceName: "parsingDataStrategy")]
    private HttpConsumingStrategy $parsingDataStrategy;

    public function __construct() {}

    public function consume(mixed $data, HTTPStatus $status) {
        if ($data instanceof ResponseBody) {
            $this->responseBodyStrategy->consume($data, $status);
        } else if ($data instanceof AvocadoResponse) {
            $this->standardResponseStrategy->consume($data, $status);
        } else {
            $this->parsingDataStrategy->consume($data, $status);
        }
    }
}