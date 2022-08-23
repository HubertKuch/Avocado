<?php

namespace Avocado\DataSource\Drivers\Connection;


use Avocado\AvocadoORM\Mappers\Mapper;
use Avocado\DataSource\Builder\Builder;
use Avocado\DataSource\Database\Statement\Statement;

interface Connection {
    public function prepare(string $sql): Statement;
    public function queryBuilder(): Builder;
    public function mapper(): Mapper;
    public function close(): void;
}
