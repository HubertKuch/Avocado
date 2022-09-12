<?php

namespace Avocado\DataSource\Drivers;

use PDO;
use Avocado\DataSource\Drivers\Connection\Connection;
use Avocado\DataSource\Drivers\Connection\MySQLConnection;

class MySQLDriver implements Driver {
    private Connection $connection;

    public function __construct(string $username, string $password, string $server, string $database, int|string
    $port, string $charset = 'utf-8') {
        $this->connection = new MySQLConnection(new PDO("mysql:host=$server;port=$port;dbname=$database;charset=$charset",
            $username,
            $password));
    }

    public function connect(): Connection {
        return $this->connection;
    }
}
