<?php

namespace Avocado\AvocadoView;

class AvocadoViewActionException extends \Exception {
    public function __construct($message, int $code = 0) {
        parent::__construct($message, $code);
    }
}