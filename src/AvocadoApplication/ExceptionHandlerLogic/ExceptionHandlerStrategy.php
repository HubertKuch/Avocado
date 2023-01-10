<?php

namespace Avocado\AvocadoApplication\ExceptionHandlerLogic;

use Exception;
use Avocado\HTTP\ResponseBody;
use Avocado\AvocadoApplication\Attributes\Exceptions\ExceptionHandler;
use Throwable;

interface ExceptionHandlerStrategy {

    public function handle(Throwable $throwable, ?ExceptionHandler $handler = null): ResponseBody;
}
