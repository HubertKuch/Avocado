<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use PHPUnit\Framework\TestCase;

require __DIR__."/mock/MockedApplication.php";
require __DIR__."/mock/MockedResource.php";
require __DIR__."/mock/MockedController.php";
require __DIR__."/mock/MockedLeafController.php";
require __DIR__."/mock/MockedConfiguration.php";

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * */
class ConfigurationTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testGetConfigurations() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run();

        $reflection = new \ReflectionClass(Application::class);

        self::assertTrue($reflection->getStaticPropertyValue("configurations")[0] instanceof Configuration);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetLeafByClass() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run();

        $reflection = new \ReflectionClass(Application::class);
        /** @var $leafManager LeafManager */
        $leafManager = $reflection->getStaticPropertyValue("leafManager");

        $resource = $leafManager->getLeafByClass(MockedLeafResource::class);

        self::assertTrue($resource instanceof MockedLeafResource);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetLeafByName() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        Application::run();

        $reflection = new \ReflectionClass(Application::class);
        /** @var $leafManager LeafManager */
        $leafManager = $reflection->getStaticPropertyValue("leafManager");

        $resource = $leafManager->getLeafByName("mocked_rsc");

        self::assertTrue($resource instanceof MockedLeafResource);
    }
}
