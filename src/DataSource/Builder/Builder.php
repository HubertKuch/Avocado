<?php

namespace Avocado\DataSource\Builder;

interface Builder {
    public function find(string $tableName, array $criteria, array $special);
    public function update(string $tableName, array $updateCriteria, array $findCriteria);
    public function delete(string $tableName, array $criteria);
    public function save(string $tableName, object $object);
}
