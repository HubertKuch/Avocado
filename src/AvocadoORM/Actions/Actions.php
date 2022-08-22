<?php

namespace Avocado\AvocadoORM\Actions;

interface   Actions {
    public function findMany(array $criteria);
    public function findFirst(array $criteria);
    public function findById(string|int $id);
    public function findOneToManyRelation(array $findCriteria, ?array $criteria);
    public function paginate(int $limit, int $offset);

    public function updateMany(array $updateCriteria, array $criteria);
    public function updateById(array $updateCriteria, string|int $id);

    public function deleteMany(array $criteria);
    public function deleteOneById(string|int $id);

    public function save(object $entity);
    public function saveMany(array $entities);

    public function truncate();
    public function renameTo(string $to);
}
