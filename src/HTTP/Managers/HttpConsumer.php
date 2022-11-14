<?php

namespace Avocado\HTTP\Managers;

use Avocado\HTTP\HTTPStatus;

/**
 * @description Interface which must be implemented by all http managers
 * */
interface HttpConsumer {
    /**
     * @description Consuming data returned by endpoint
     * */
    function consume(mixed $data, HTTPStatus $status);
}