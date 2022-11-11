<?php

namespace Avocado\DataSource;

use Avocado\DataSource\Drivers\Driver;

class DataSource {
    private Driver $driver;

    public function __construct(Driver $driver) {
        $this->driver = $driver;
    }

    public function getDriver(): Driver {
        return $this->driver;
    }
}
