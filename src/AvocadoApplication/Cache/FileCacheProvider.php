<?php

namespace Avocado\AvocadoApplication\Cache;

use Avocado\Application\Application;
use Avocado\AvocadoApplication\AutoConfigurations\CacheConfiguration;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\Resource;

#[Resource]
class FileCacheProvider implements CacheProvider {
    #[Autowired]
    private readonly CacheConfiguration $cacheConfiguration;

    public function saveItem(string $key, mixed $value, bool $override = false): bool {
        $fullPath = $this->generateFullFilePath($key);

        if (file_exists($fullPath) && !$override) {
            return false;
        }

        $exported = var_export($value, true);
        $phpCode = "<?php return\n " . $exported . ";\n";

        return (bool)file_put_contents($fullPath, $phpCode, LOCK_EX);
    }

    public function getItem(string $key): mixed {
        $fullFilePath = $this->generateFullFilePath($key);

        if (!file_exists($fullFilePath)) {
            return null;
        }

        return @include $fullFilePath;
    }

    public function isExists(string $key): bool {
        return file_exists($this->generateFullFilePath($key));
    }

    private function generateFileName(string $key): string {
        return md5($key) . ".lock";
    }

    private function generateFullFilePath(string $key): string {
        return Application::getProjectDirectory() . "/" . $this->cacheConfiguration->getCacheDir() . "/" . $this->generateFileName($key);
    }

    public function delete(string $key): bool {
        $fullPath = $this->generateFullFilePath($key);

        return unlink($fullPath);
    }
}