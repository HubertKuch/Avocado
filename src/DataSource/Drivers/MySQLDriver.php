<?php

namespace Avocado\DataSource\Drivers;

use Avocado\DataSource\Drivers\Connection\Connection;
use Avocado\DataSource\Drivers\Connection\MySQLConnection;
use PDO;

class MySQLDriver implements Driver {
    private Connection $connection;

    public function __construct(string $username, string $password, string $server, string $database, int|string $port) {
        $this->connection = new MySQLConnection(new PDO("mysql:host=$server;port=$port;dbname=$database", $username, $password));
    }

    public function connect(): Connection {
        return $this->connection;
    }
}
