<?php

namespace Avocado\ORM;

class AvocadoORMSettings {
    private static array $settings = array(
        "CONNECTION" => null,
    );

    /**
     * @throws AvocadoRepositoryException
     */
    public static function useDatabase(string $dsn, string $user, string $pass): void {
        try {
            self::$settings['CONNECTION'] = new \PDO($dsn, $user, $pass);
        } catch (\PDOException $e) {
            throw new AvocadoRepositoryException("Database connection fail: $e->errorInfo");
        }
    }

    protected static function _getConnection(): \PDO {
        return self::$settings['CONNECTION'];
    }
}
