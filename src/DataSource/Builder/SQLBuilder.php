<?php

namespace Avocado\DataSource\Builder;

interface SQLBuilder extends Builder {
    public function find(string $tableName, array $criteria, array $special): string;
    public function update(string $tableName, array $updateCriteria, array $findCriteria): string;
    public function delete(string $tableName, array $criteria): string;
    public function save(string $tableName, object $object): string;
}
