<?php

namespace Avocado\ORM;

use Avocado\DataSource\DataSource;
use Avocado\DataSource\Drivers\Connection\Connection;
use Avocado\DataSource\Drivers\Driver;

class AvocadoORMSettings {
    private static array $settings = array(
        "CONNECTION"    => null,
        "DRIVER"        => null,
        "DATA_SOURCE"   => null,
    );

    public static function fromExistingSource(DataSource $dataSource): void {
        self::$settings['DATA_SOURCE'] = $dataSource;
        self::$settings['CONNECTION'] = $dataSource->getDriver()->connect();
        self::$settings['DRIVER'] = $dataSource->getDriver();
    }

    protected static function getConnection(): Connection {
        return self::$settings['CONNECTION'];
    }

    protected static function getDriver(): Driver {
        return self::$settings['DRIVER'];
    }
}
