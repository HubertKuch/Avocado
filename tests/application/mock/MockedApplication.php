<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Avocado;
use Avocado\AvocadoApplication\Attributes\Exclude;

#[Avocado]
class MockedApplication {

    public static function init(): void {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run(dirname(__DIR__, 2));
    }
}
