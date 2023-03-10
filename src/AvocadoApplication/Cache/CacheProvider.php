<?php

namespace Avocado\AvocadoApplication\Cache;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\AutoConfigurations\CacheConfiguration;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\Resource;
use Exception;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;

#[Resource]
class CacheProvider {
    #[Autowired]
    private readonly CacheConfiguration $cacheConfiguration;
    private ExtendedCacheItemPoolInterface $cacheManager;

    public function init(): void {
        $cacheManagerConfiguration = $this->getCacheManagerConfiguration();
        CacheManager::setDefaultConfig($cacheManagerConfiguration);

        $this->cacheManager = CacheManager::getInstance($this->cacheConfiguration->getCacheDriver()->value);
    }

    public function get(string $key): mixed {
        try {
            $item = $this->cacheManager->getItem($key);

            return $item->isExpired() ? null : $item->get();
        } catch (Exception) {
            return null;
        }
    }

    public function set(string $key, mixed $value, int $expiresAfterSeconds = 5): void {
        try {
            $item = $this->cacheManager->getItem($key);

            $cacheInstance = $item->set($value)->expiresAfter($expiresAfterSeconds);

            $this->cacheManager->save($cacheInstance);
        } catch (Exception) {
        }
    }

    private function getCacheManagerConfiguration(): ConfigurationOption {
        return new ConfigurationOption(["path" => Application::getProjectDirectory() . $this->cacheConfiguration->getCacheDir(), "preventCacheSlams" => true]);
    }
}