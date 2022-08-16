<?php

namespace Avocado\DataSource\Drivers\Connection;

use Avocado\DataSource\Database\Statement\MySQLStatement;
use Avocado\DataSource\Database\Statement\Statement;
use PDO;

class MySQLConnection implements Connection {
    private ?PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function prepare(string $sql): Statement {
        return new MySQLStatement($this->pdo, $sql);
    }

    public function close(): void {
        $this->pdo = null;
    }

    public function getPDO(): PDO {
        return $this->pdo;
    }
}
