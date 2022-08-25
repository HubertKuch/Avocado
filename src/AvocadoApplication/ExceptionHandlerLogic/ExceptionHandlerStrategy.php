<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Avocado\HTTP\ResponseBody;
use Exception;

interface ExceptionHandlerStrategy {

    public function handle(Exception $exception, ?ExceptionHandler $handler = null): ResponseBody;
}
