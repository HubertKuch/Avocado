<?php

namespace Avocado\ORM;

use Avocado\DataSource\DataSource;
use PDO;

class AvocadoORMSettings {
    private static array $settings = array(
        "CONNECTION"    => null,
        "DRIVER"        => null,
    );

    /**
     * @throws AvocadoRepositoryException
     */
    public static function useDatabase(string $dsn, string $user, string $pass): void {
        try {
            self::$settings['CONNECTION'] = new PDO($dsn, $user, $pass);
        } catch (\PDOException $e) {
            throw new AvocadoRepositoryException("Database connection fail: $e->errorInfo");
        }
    }

    public static function fromExistingSource(DataSource $dataSource): void {
        self::$settings['CONNECTION'] = $dataSource->getDriver()->connect()->getPDO();
    }

    protected static function _getConnection(): PDO {
        return self::$settings['CONNECTION'];
    }
}
