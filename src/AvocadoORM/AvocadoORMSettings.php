<?php

namespace Avocado\ORM;

use Avocado\DataSource\DataSource;
use Avocado\DataSource\Drivers\Connection\Connection;

class AvocadoORMSettings {
    private static array $settings = array(
        "CONNECTION"    => null,
        "DRIVER"        => null,
    );

    public static function fromExistingSource(DataSource $dataSource): void {
        self::$settings['CONNECTION'] = $dataSource->getDriver()->connect();
    }

    protected static function _getConnection(): Connection {
        return self::$settings['CONNECTION'];
    }
}
