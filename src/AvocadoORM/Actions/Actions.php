<?php

namespace Avocado\AvocadoORM\Actions;

use Avocado\AvocadoORM\Order;

/**
 * @template T
 * */
interface Actions {
    /**
    * @return T[]
     */
    public function findMany(array $criteria, string $orderBy = null, Order $order = Order::ASCENDING): array;
    /**
     * @return T
     */
    public function findFirst(array $criteria);
    /**
     * @return T
     */
    public function findById(string|int $id);
    /**
     * @return T[]
     */
    public function paginate(int $limit, int $offset): array;
    public function updateMany(array $updateCriteria, array $criteria);
    public function updateById(array $updateCriteria, string|int $id);

    public function deleteMany(array $criteria);
    public function deleteOneById(string|int $id);

    /**
     * @param T $entity
     */
    public function save(object $entity);
    public function transactionSave(object $entity);
    /**
     * @param T[] $entities
     * */
    public function saveMany(array $entities);
    public function transactionSaveMany(array $entities);
    /**
     * @template V
     * @param class-string<V> $type
     * @return V
     * */
    public function customWithSingleDataset(string $query, string $type = null);
    /**
     * @return T[]
     * */
    public function customWithDataset(string $query): array;
    public function custom(string $query);
}
