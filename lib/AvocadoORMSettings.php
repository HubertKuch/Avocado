<?php

namespace Avocado\ORM;

class AvocadoORMSettings {
    private static array $settings = array(
        "FETCH_OPTION" => 2,
        "CONNECTION" => null
    );

    public static function useDatabase(string $dsn, string $user, string $pass) {
        self::$settings['CONNECTION'] = new \PDO($dsn, $user, $pass);
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
