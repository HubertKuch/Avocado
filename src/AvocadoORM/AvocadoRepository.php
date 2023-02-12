<?php

namespace Avocado\ORM;

use Avocado\AvocadoORM\Actions\Actions;
use Avocado\AvocadoORM\Order;
use Avocado\Tests\Unit\TestUser;
use Avocado\Utils\TypesUtils;
use ReflectionClass;
use ReflectionException;

/**
 * @template T
 * @implements Actions<T>
 */
class AvocadoRepository extends AvocadoModel implements Actions {
    const EXCEPTION_UPDATE_CRITERIA_MESSAGE = "Update criteria don't have to be empty.";
    const TABLE = __NAMESPACE__ . "\Attributes\Table";
    const ID = __NAMESPACE__ . "\Attributes\Id";
    const FIELD = __NAMESPACE__ . "\Attributes\Field";
    const IGNORE_FIELD_TYPE = __NAMESPACE__ . "\Attributes\IgnoreFieldType";

    public function __construct(string $model) {
        parent::__construct($model);
    }

    /**
     * @param string $query
     * @return T[]
     */
    private function queryWithMapper(string $query): array {
        $stmt = self::getConnection()->prepare($query);
        $data = $stmt->execute();
        $entities = [];

        $mapper = self::getConnection()->mapper();

        foreach ($data as $entity) {
            $entities[] = $mapper->entityToObject($this, $entity);
        }

        return $entities;
    }

    private function query(string $query): array {
        $stmt = self::getConnection()->prepare($query);

        return $stmt->execute();
    }

    /**
     * @param array $criteria
     * @param string|null $orderBy
     * @param Order $order
     * @return array<T>
     */
    public function findMany(array $criteria = [], string $orderBy = null, Order $order = Order::ASCENDING): array {
        $query = parent::getConnection()->queryBuilder()->find($this->tableName, $criteria, []);

        if ($orderBy !== null) {
            $query->orderBy($orderBy, $order);
        }

        return ($this->queryWithMapper($query->get())) ?: [];
    }

    /**
     * @param array $criteria
     * @return T|null
     */
    public function findFirst(array $criteria = []): ?object {
        $query = parent::getConnection()->queryBuilder()->find($this->tableName, $criteria, [])->get();
        $query .= " LIMIT 1";

        $res = $this->queryWithMapper($query);

        return empty($res) ? null : $res[0];
    }

    /**
     * @param int|string $id
     * @template T
     * @return T|null
     */
    public function findById(int|string $id): ?object {
        $query = parent::getConnection()->queryBuilder()->find($this->tableName, [$this->primaryKey => $id], [])->get();

        $res = $this->queryWithMapper($query);

        return empty($res) ? null : $res[0];
    }

    public function paginate(int $limit, int $offset): array {
        $query = parent::getConnection()
                       ->queryBuilder()
                       ->find($this->tableName, [], [])
                       ->limit($limit)
                       ->offset($offset)
                       ->get();

        return $this->queryWithMapper($query . " LIMIT $limit OFFSET $offset");
    }

    /**
     * @throws AvocadoRepositoryException
     */
    public function updateMany(array $updateCriteria, array $criteria = []) {
        if (empty($updateCriteria)) {
            throw new AvocadoRepositoryException(self::EXCEPTION_UPDATE_CRITERIA_MESSAGE);
        }

        $query = parent::getConnection()->queryBuilder()->update($this->tableName, $updateCriteria, $criteria)->get();

        $this->queryWithMapper($query);
    }

    /**
     * @throws AvocadoModelException
     */
    public function updateById(array $updateCriteria, string|int $id) {
        if (empty($updateCriteria)) {
            throw new AvocadoModelException(self::EXCEPTION_UPDATE_CRITERIA_MESSAGE);
        }

        $query = parent::getConnection()
                       ->queryBuilder()
                       ->update($this->tableName, $updateCriteria, [$this->primaryKey => $id])
                       ->get();

        $this->queryWithMapper($query);
    }


    /**
     * @param array $criteria
     * @return void
     */
    public function deleteMany(array $criteria): void {
        $query = parent::getConnection()->queryBuilder()->delete($this->tableName, $criteria)->get();

        $this->queryWithMapper($query);
    }

    /**
     * @param int|string $id
     * @return void
     */
    public function deleteOneById(int|string $id): void {
        $query = parent::getConnection()->queryBuilder()->delete($this->tableName, [$this->primaryKey => $id])->get();

        $this->queryWithMapper($query);
    }

    /**
     * @param object $object
     * @return string
     */
    private function getInsertColumns(object $object): string {
        $ref = new ReflectionClass($object);
        $columnStatement = "(";

        foreach ($ref->getProperties() as $property) {
            $propertyName = $property->getName();

            if (!empty($property->getAttributes(self::FIELD))) {
                if (!empty($property->getAttributes(self::FIELD)[0]->getArguments())) {
                    $propertyName = $property->getAttributes(self::FIELD)[0]->getArguments()[0];
                }
            }

            $columnStatement .= "$propertyName,";
        }

        if (str_ends_with($columnStatement, ",")) {
            $columnStatement = substr($columnStatement, 0, -1);
        }

        return $columnStatement . ")";
    }

    /**
     * @param object $entity
     * @return void
     */
    public function save(object $entity): void {
        $query = parent::getConnection()->queryBuilder()::save($this->tableName, $entity)->get();

        parent::getConnection()->prepare($query)->execute();
    }

    /**
     * @param ...$entities
     * @return void
     */
    public function saveMany(...$entities): void {
        foreach ($entities as $entity) {
            $this->save($entity);
        }
    }

    public function customWithSingleDataset(string $query, string $type = null): ?object {
        if ($type !== null && TypesUtils::stringContainsPrimitiveType($type)) {
            $dataset = $this->query($query);

            if (empty($dataset)) {
                return null;
            }

            $vars = get_object_vars($dataset[0]);

            return $dataset[0]->{$vars[key($vars)]};
        }

        $dataset = $this->queryWithMapper($query);

        return empty($dataset) ? null : $dataset[0];
    }

    public function customWithDataset(string $query): array {
        return $this->queryWithMapper($query);
    }

    public function custom(string $query) {
        $this->query($query);
    }
}
