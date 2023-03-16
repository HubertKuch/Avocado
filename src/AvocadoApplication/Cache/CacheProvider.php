<?php

namespace Avocado\AvocadoApplication\Cache;

interface CacheProvider {
     /**
     * @param string $key Cache file key
     * @param mixed $value Value will be mapped by <b>var_export</b> method
     * @param bool $override If file exists then override
     * @return bool Returns whether saved
     * */
    public function saveItem(string $key, mixed $value, bool $override = false): bool;

    /**
     * @param string $key Key by which get value
     * */
    public function getItem(string $key): mixed;

    /**
     * @param string $key Checks is exists
     * */
    public function isExists(string $key): bool;

    /**
     * @param string $key Deleted cache by key
     * */
    public function delete(string $key): bool;
}