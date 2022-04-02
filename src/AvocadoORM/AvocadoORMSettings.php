<?php

namespace Avocado\ORM;

class AvocadoORMSettings {
    private static array $settings = array(
        "FETCH_OPTION" => 2,
        "CONNECTION" => null
    );

    /**
     * @throws AvocadoRepositoryException
     */
    public static function useDatabase(string $dsn, string $user, string $pass) {
        try {
            self::$settings['CONNECTION'] = new \PDO($dsn, $user, $pass);
        } catch (\PDOException $e) {
            throw new AvocadoRepositoryException("Database connection fail: $e->errorInfo");
        }
    }

    public static function useFetchOption(int $option) {
        self::$settings['FETCH_OPTION'] = $option;
    }

    protected static function _getConnection(): \PDO {
        return self::$settings['CONNECTION'];
    }

    protected static function _getFetchOption(): int {
        return self::$settings['FETCH_OPTION'];
    }
}
