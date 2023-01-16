<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Utils\AvocadoClassFinderUtil;
use PHPUnit\Framework\TestCase;
use Avocado\AvocadoApplication\AutoControllers\ExceptionsAutoController;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class ClassAutoLoaderTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testGettingClassesNames() {
        $_SERVER['REQUEST_METHOD'] = "GET";

        $classes = AvocadoClassFinderUtil::getDeclaredClasses(dirname(__DIR__), [ExceptionsAutoController::class]);

        self::assertNotEmpty($classes);
    }
}
