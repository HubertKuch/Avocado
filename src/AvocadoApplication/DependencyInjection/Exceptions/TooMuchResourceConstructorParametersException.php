<?php

namespace AvocadoApplication\DependencyInjection\Exceptions;

use Exception;
use Throwable;

class TooMuchResourceConstructorParametersException extends Exception {
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
