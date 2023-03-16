<?php

namespace Avocado\DataSource\Builder;

use Avocado\AvocadoORM\Attributes\JoinDirection;
use Avocado\AvocadoORM\Order;

interface SQLBuilder extends Builder {
    public static function find(string $tableName, array $criteria, ?array $special): Builder;

    public function join(string $table, string $columnName, string $referencedColumnName, JoinDirection $joinDirection = JoinDirection::LEFT): Builder;

    public static function update(string $tableName, array $updateCriteria, array $findCriteria): Builder;

    public static function delete(string $tableName, array $criteria): Builder;

    public static function save(string $tableName, object $object): Builder;

    public function limit(int $limit): Builder;

    public function offset(int $offset): Builder;

    public function orderBy(string $field, Order $order): Builder;

    public function get(): string;
}
