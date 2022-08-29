<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Leafs\LeafManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
        MockedApplication::init(__DIR__);

        $reflection = new ReflectionClass(Application::class);

        self::assertTrue($reflection->getStaticPropertyValue("configurations")[0] instanceof Configuration);
    }

    /**
     * @runInSeparateProcess
     * */
    public function testGetLeafByClass() {
        $_SERVER['REQUEST_METHOD'] = "GET";
        MockedApplication::init(__DIR__);

        $reflection = new ReflectionClass(Application::class);
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
        MockedApplication::init(__DIR__);

        $reflection = new ReflectionClass(Application::class);
        /** @var $leafManager LeafManager */
        $leafManager = $reflection->getStaticPropertyValue("leafManager");

        $resource = $leafManager->getLeafByName("mocked_rsc");

        self::assertTrue($resource instanceof MockedLeafResource);
    }
}
