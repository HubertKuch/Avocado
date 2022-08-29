<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Exception;
use Avocado\HTTP\ResponseBody;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;

interface ExceptionHandlerStrategy {

    public function handle(Exception $exception, ?ExceptionHandler $handler = null): ResponseBody;
}
