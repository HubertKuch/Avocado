<?php

namespace Avocado\AvocadoApplication\Cache;

use Avocado\Application\Application;
use PHPUnit\Framework\TestCase;

class FileCacheProviderTest extends TestCase {

    /**
     * @runInSeparateProcess
     * */
    public function testGetItem() {
        CacheMockedApplication::init();
        $provider = Application::getCacheProvider();

        self::assertNull($provider->getItem("random_key_243891470194294"));
    }

    /**
     * @runInSeparateProcess
     * */
    public function testSaveItem() {
        CacheMockedApplication::init();
        $provider = Application::getCacheProvider();

        $provider->saveItem("test", [2, 4, 10]);

        $value = $provider->getItem("test");

        self::assertEquals([2, 4, 10], $value);
        $provider->delete("test");
    }

    /**
     * @runInSeparateProcess
     * */
    public function testIsExists() {
        CacheMockedApplication::init();
        $provider = Application::getCacheProvider();

        $provider->saveItem("test", [2, 4, 10]);
        $isExists = $provider->isExists("test");

        self::assertTrue($isExists);
        $provider->delete("test");
    }

    /**
     * @runInSeparateProcess
     * */
    public function testDelete() {
        CacheMockedApplication::init();
        $provider = Application::getCacheProvider();

        $provider->saveItem("test", [2, 4, 10]);
        $isDeleted = $provider->delete("test");

        self::assertTrue($isDeleted);
    }
}
