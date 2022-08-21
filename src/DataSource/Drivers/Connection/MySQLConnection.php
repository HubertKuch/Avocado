<?php

namespace Avocado\DataSource\Drivers\Connection;

use Avocado\DataSource\Builder\SQLBuilder;
use PDO;
use Avocado\DataSource\Builder\Builder;
use Avocado\DataSource\Builder\MySQLQueryBuilder;
use Avocado\DataSource\Database\Statement\MySQLStatement;
use Avocado\DataSource\Database\Statement\Statement;

class MySQLConnection implements Connection {
    private ?PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function queryBuilder(): SQLBuilder {
        return new MySQLQueryBuilder();
    }

    public function prepare(string $sql): Statement {
        return new MySQLStatement($this->pdo, $sql);
    }

    public function close(): void {
        $this->pdo = null;
    }
}
