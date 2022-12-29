<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Avocado;

require "vendor/autoload.php";

#[Avocado]
class App {

    public function __construct() {

        Application::run(__DIR__);

    }

}


new App();