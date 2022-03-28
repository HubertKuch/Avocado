<?php

use JetBrains\PhpStorm\Pure;

class TableNameException extends Exception {
    #[Pure] public function __construct($message, int $code = 0) {
        parent::__construct($message, $code);
    }
}

class AvocadoModelException extends Exception {
    #[Pure] public function __construct($message, int $code = 0) {
        parent::__construct($message, $code);
    }
}