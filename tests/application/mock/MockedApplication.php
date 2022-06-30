<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;

class MockedApplication {
    public static function init(): void {
        Application::run();
    }
}
