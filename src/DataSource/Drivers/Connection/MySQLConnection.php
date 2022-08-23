<?php

namespace Avocado\DataSource\Drivers\Connection;

use Avocado\AvocadoORM\Mappers\Mapper;
use Avocado\AvocadoORM\Mappers\MySQLMapper;
use Avocado\DataSource\Builder\SQLBuilder;
use PDO;
use Avocado\DataSource\Builder\MySQLQueryBuilder;
use Avocado\DataSource\Database\Statement\MySQLStatement;
use Avocado\DataSource\Database\Statement\Statement;

class MySQLConnection implements Connection {
    private ?PDO $pdo;
    private Mapper $mapper;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->mapper = new MySQLMapper();
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

    public function mapper(): MySQLMapper {
        return $this->mapper;
    }
}
