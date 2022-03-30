<?php

namespace Avocado\ORM;

interface AvocadoRepositoryActions {
    public function findMany(array $criteria);
    public function findOne(array $criteria);
    public function findOneById(string|int $id);
    public function findOneToManyRelation(array $findCriteria, ?array $criteria);

    public function updateMany(array $updateCriteria, array $criteria);
    public function updateOne(array $updateCriteria, array $criteria);
    public function updateOneById(array $updateCriteria, string|int  $id);

    public function deleteMany(array $criteria);
    public function deleteOneById(string|int $id);

    public function save(object $entity);
    public function saveMany(array $entities);

    public function truncate();
    public function renameTo(string $to);
}
