<?php

namespace Avocado\HTTP\Managers;

use Avocado\HTTP\ContentType;
use Avocado\HTTP\HTTPStatus;
use Avocado\HTTP\ResponseBody;

/**
 * @description Interface which must be implemented by all http managers
 * */
interface HttpConsumer {
    /**
     * @description Consuming data returned by endpoint
     * */
    function consume(ResponseBody $responseBody);
}