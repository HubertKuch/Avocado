<?php

namespace Avocado\DataSource\Database\Statement;

use Avocado\AvocadoORM\Mappers\Mapper;
use Avocado\AvocadoORM\Mappers\MySQLMapper;
use PDO;

class MySQLStatement implements Statement {
    private string $sql;
    private PDO $pdo;

    public function __construct(PDO $pdo, string $sql) {
        $this->pdo = $pdo;
        $this->sql = $sql;
    }

    public function execute(): array {
        $stmt = $this->pdo->query($this->sql);

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    public function mapper(): Mapper {
        return new MySQLMapper();
    }
}
