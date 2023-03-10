<?php

namespace Avocado\AvocadoApplication\AutoConfigurations;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\ConfigurationProperties;
use Avocado\AvocadoApplication\Cache\CacheDriver;

#[Configuration]
#[ConfigurationProperties(prefix: "cache")]
final class CacheConfiguration {
    private string $cacheDir = "/cache";
    private CacheDriver $cacheDriver = CacheDriver::FILES;

    public function getCacheDir(): string {
        return $this->cacheDir;
    }

    public function getCacheDriver(): CacheDriver {
        return $this->cacheDriver;
    }
}