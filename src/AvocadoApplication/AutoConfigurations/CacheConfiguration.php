<?php

namespace Avocado\AvocadoApplication\AutoConfigurations;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\ConfigurationProperties;

#[Configuration]
#[ConfigurationProperties(prefix: "cache")]
final class CacheConfiguration {
    private string $cacheDir = "/cache";

    public function getCacheDir(): string {
        return $this->cacheDir;
    }
}