<?php

namespace Avocado\AvocadoView;

use JetBrains\PhpStorm\Pure;

class AvocadoViewException extends \Exception {
    #[Pure] public function __construct($message, int $code = 0) {
        parent::__construct($message, $code);
    }
}