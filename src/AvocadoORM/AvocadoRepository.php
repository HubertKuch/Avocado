<?php

namespace Avocado\ORM;

use Avocado\AvocadoORM\Actions\Actions;
use Avocado\AvocadoORM\Attributes\Relations\JoinColumn;
use Avocado\AvocadoORM\Attributes\Relations\ManyToOne;
use Avocado\AvocadoORM\Attributes\Relations\OneToMany;
use Avocado\AvocadoORM\Attributes\Relations\OneToOne;
use Avocado\AvocadoORM\Order;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\ReflectionUtils;
use Avocado\Utils\TypesUtils;
use ReflectionClass;
use ReflectionObject;
use stdClass;
use Throwable;

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
            $mappedEntity = $mapper->entityToObject($this, $entity);
            $ref = new ReflectionObject($mappedEntity);

            $entities[] = $this->resolveRelations($entity, $mappedEntity);
        }

        return $entities;
    }

    private function resolveRelations(stdClass $plain, object $mappedEntity): object {
        $oneToManyColumns = parent::getJoinedProperties(OneToMany::class);
        $oneToOneColumns = parent::getJoinedProperties(OneToOne::class);
        $manyToOneColumns = parent::getJoinedProperties(ManyToOne::class);

        foreach ($oneToManyColumns as $column) {
            $joinColumn = AnnotationUtils::getInstance($column, JoinColumn::class);
            $oneToMany = AnnotationUtils::getInstance($column, OneToMany::class);

            $query = self::getConnection()->queryBuilder()::find($joinColumn->getTable(),
                [$joinColumn->getName() => $plain->{$joinColumn->getReferencesTo()}],
                [])->get();

            $dataToJoin = (new AvocadoRepository($oneToMany->getClass()))->customWithDataset($query);

            $column->setValue($mappedEntity, $dataToJoin);
        }

        foreach ($oneToOneColumns as $column) {
            $joinColumn = AnnotationUtils::getInstance($column, JoinColumn::class);
            $oneToOne = AnnotationUtils::getInstance($column, OneToOne::class);

            $query = self::getConnection()->queryBuilder()::find($joinColumn->getTable(),
                [$joinColumn->getReferencesTo() => $plain->{$joinColumn->getName()}],
                [])->get();

            $type = $column->getType()->getName();

            $dataToJoin = (new AvocadoRepository($type))->customWithSingleDataset($query, $type);

            $column->setValue($mappedEntity, $dataToJoin);
        }

        foreach ($manyToOneColumns as $column) {
            $joinColumn = AnnotationUtils::getInstance($column, JoinColumn::class);

            $query = self::getConnection()->queryBuilder()::find($joinColumn->getTable(),
                [$joinColumn->getReferencesTo() => $plain->{$joinColumn->getName()}],
                [])->get();

            $type = $column->getType()->getName();
            $dataToJoin = (new AvocadoRepository($type))->customWithSingleDataset($query, $type);
            $column->setValue($mappedEntity, $dataToJoin);
        }

        return $mappedEntity;
    }

    private function query(string $query): array {
        $stmt = self::getConnection()->prepare($query);

        return $stmt->execute();
    }

    /**
     * @param array $criteria
     * @param string|null $orderBy
     * @param Order $order
     * @return T[]
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
     * @param T $entity
     * @return void
     */
    public function save(object $entity): void {
        $oneToManyColumns = parent::getJoinedProperties(OneToMany::class);
        $oneToOneColumns = parent::getJoinedProperties(OneToOne::class);
        $manyToOneColumns = parent::getJoinedProperties(ManyToOne::class);

        foreach ($oneToOneColumns as $column) {
            $value = $column->getValue($entity);
            $valueRef = new ReflectionObject($value);
            $join = AnnotationUtils::getInstance($column, JoinColumn::class);

            $valueRef->getProperty($join->getReferencesTo())
                     ->setValue($value, $this->ref->getProperty($join->getName())->getValue($entity));

            $type = $column->getType()->getName();
            $repo = new AvocadoRepository($type);

            $repo->save($value);
        }

        foreach ($manyToOneColumns as $column) {
            $value = $column->getValue($entity);
            $join = AnnotationUtils::getInstance($column, JoinColumn::class);

            $type = $column->getType()->getName();
            $repo = new AvocadoRepository($type);

            $repo->save($value);
        }

        foreach ($oneToManyColumns as $column) {
            $oneToMany = AnnotationUtils::getInstance($column, OneToMany::class);
            $type = $oneToMany->getClass();

            $repo = new AvocadoRepository($type);

            $repo->saveMany($column->getValue($entity));
        }

        $primaryKeyValue = $this->ref->getProperty($this->primaryKey)->getValue($entity);

        if ($this->findFirst([$this->primaryKey => $primaryKeyValue]) === null) {
            $query = parent::getConnection()->queryBuilder()::save($this->tableName, $entity)->get();
        } else {
            $updateCriteria = ReflectionUtils::modelFieldsToArray($entity, true);

            $query = parent::getConnection()->queryBuilder()::update($this->tableName,
                $updateCriteria,
                [$this->primaryKey => $primaryKeyValue])->get();
        }

        parent::getConnection()->prepare($query)->execute();
    }

    public function transactionSave(object $entity): void {
        if (self::getConnection()->transactionManager()->begin()) {
            try {
                $this->save($entity);

                self::getConnection()->transactionManager()->commit();
            } catch (Throwable $throwable) {
                self::getConnection()->transactionManager()->rollback();
                throw $throwable;
            }
        }
    }

    public function transactionSaveMany(array $entities): void {
        if (self::getConnection()->transactionManager()->begin()) {
            try {
                $this->saveMany($entities);

                self::getConnection()->transactionManager()->commit();
            } catch (Throwable) {
                self::getConnection()->transactionManager()->rollback();
            }
        }
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
