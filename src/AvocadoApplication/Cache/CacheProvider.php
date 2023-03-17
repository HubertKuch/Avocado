<?php

namespace Avocado\AvocadoApplication\Cache;

interface CacheProvider {
    /**
     * @param string|object $key Cache file key, if enum gets value which must be a string
     * @param mixed $value Value will be mapped by <b>var_export</b> method
     * @param bool $override If file exists then override
     * @return bool Returns whether saved
     */
    public function saveItem(string|object $key, mixed $value, bool $override = false): bool;

    /**
     * @param string|object $key Key by which get value, if enum gets value which must be a string
     * @return mixed
     */
    public function getItem(string|object $key): mixed;

    /**
     * @param string|object $key Checks is exists, if enum gets value which must be a string
     * @return bool
     */
    public function isExists(string|object $key): bool;

    /**
     * @param string|object $key Deleted cache by key, if enum gets value which must be a string
     * @return bool
     */
    public function delete(string|object $key): bool;
}