<?php

class TableNameException extends Exception {
    public function __construct($message, int $code = 0) {
        parent::__construct($message, $code);
    }
}

class AvocadoModelException extends Exception {
    public function __construct($message, int $code = 0) {
        parent::__construct($message, $code);
    }
}

class AvocadoRepositoryException extends Exception {
    public function __construct($message, int $code = 0) {
        parent::__construct($message, $code);
    }
}
