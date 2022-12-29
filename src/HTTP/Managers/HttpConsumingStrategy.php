<?php

namespace Avocado\HTTP\Managers;

use Avocado\HTTP\ResponseBody;

interface HttpConsumingStrategy {

    function consume(ResponseBody $responseBody): void;

}