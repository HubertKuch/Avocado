<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Leaf;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use PHPUnit\Framework\TestCase;

require __DIR__."/mock/MockedApplication.php";
require __DIR__."/mock/MockedController.php";
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
        MockedApplication::init();
        $reflection = new \ReflectionClass(Application::class);

        self::assertTrue($reflection->getStaticPropertyValue("configurations")[0] instanceof Configuration);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetLeafByClass() {
        MockedApplication::init();
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
        MockedApplication::init();
        $reflection = new \ReflectionClass(Application::class);
        /** @var $leafManager LeafManager */
        $leafManager = $reflection->getStaticPropertyValue("leafManager");

        $resource = $leafManager->getLeafByName("mocked_rsc");

        self::assertTrue($resource instanceof MockedLeafResource);
    }
}
