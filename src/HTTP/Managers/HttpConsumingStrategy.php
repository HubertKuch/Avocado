<?php

namespace Avocado\HTTP\Managers;

use Avocado\HTTP\HTTPStatus;

interface HttpConsumingStrategy {

    function consume(mixed $data, HTTPStatus $status): void;

}