<?php

namespace Avocado\DataSource\Database\Statement;

use PDO;

class MySQLStatement implements Statement {
    private string $sql;
    private PDO $pdo;

    public function __construct(PDO $pdo, string $sql) {
        $this->pdo = $pdo;
        $this->sql = $sql;
    }

    public function execute(): array {
        $this->pdo->exec($this->sql);
    }
}
