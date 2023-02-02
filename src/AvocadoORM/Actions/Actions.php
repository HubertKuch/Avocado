<?php

namespace Avocado\AvocadoORM\Actions;

use Avocado\AvocadoORM\Order;

interface Actions {
    public function findMany(array $criteria, string $orderBy = null, Order $order = Order::ASCENDING);
    public function findFirst(array $criteria);
    public function findById(string|int $id);
    public function paginate(int $limit, int $offset);

    public function updateMany(array $updateCriteria, array $criteria);
    public function updateById(array $updateCriteria, string|int $id);

    public function deleteMany(array $criteria);
    public function deleteOneById(string|int $id);

    public function save(object $entity);
    public function saveMany(array $entities);
    public function customWithSingleDataset(string $query, string $type = null);
    public function customWithDataset(string $query);
    public function custom(string $query);
}
