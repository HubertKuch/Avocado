<?php

namespace Avocado\ORM;

use PDO;
use ReflectionException;

/**
 * @template T
 */
class AvocadoRepository extends AvocadoModel implements AvocadoRepositoryActions {
    const EXCEPTION_UPDATE_CRITERIA_MESSAGE = "Update criteria don't have to be empty.";
    const TABLE = __NAMESPACE__."\Attributes\Table";
    const ID = __NAMESPACE__."\Attributes\Id";
    const FIELD = __NAMESPACE__."\Attributes\Field";
    const IGNORE_FIELD_TYPE = __NAMESPACE__."\Attributes\IgnoreFieldType";

    /**
     * @param class-string<T> $model
     * @return void
     */
    public function __construct($model) {
        parent::__construct($model);
    }

    /**
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    private function checkIsCriteriaTypesAreCompatibleWithModel(array $criteria) {
        foreach ($criteria as $key => $value) {
            $propertyNotFoundMessage = sprintf("%s model does not have %s property. Add it to model with #[Field] attribute", $this->model, $key);
            $propertyTypeIsDifferentMessage = sprintf("%s property is not %s type on %s model", $key, gettype($value), $this->model);

            if (!self::isModelHasProperty($key)) {
                throw new AvocadoRepositoryException($propertyNotFoundMessage);
            }

            if (!self::isModelPropertyIsType($key, gettype($value))) {
                throw new AvocadoRepositoryException($propertyTypeIsDifferentMessage);
            }
        }
    }

    /**
     * @throws ReflectionException
     * @throws AvocadoRepositoryException
     */
    private function provideCriteria(string &$sql, array $criteria): void {
        $this->checkIsCriteriaTypesAreCompatibleWithModel($criteria);

        $sql.= " WHERE ";
        foreach ($criteria as $key => $value) {
            $valueType = gettype($value);

            if ($valueType === "integer" || $valueType === "double" || $valueType === "boolean") $sql.=" $key = $value AND ";
            else if ($valueType === "string") $sql.= " $key LIKE \"$value\" AND";
        }

        $sql = substr($sql, 0,-4);
    }

    /**
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    private function provideUpdateCriteria(string &$sql, array $criteria): void {
        $this->checkIsCriteriaTypesAreCompatibleWithModel($criteria);

        foreach ($criteria as $key => $value) {
            $valueType = gettype($value);

            if ($valueType === "integer" || $valueType === "double" || $valueType === "boolean") $sql.=" $key = $value, ";
            else if ($valueType === "string") $sql.= " $key = \"$value\" , ";
        }

        $sql = substr($sql, 0, -2);
    }

    /**
     * @param array|FindForeign $findCriteria
     * @return string
     */
    private function formatSubQuery(array|FindForeign $findCriteria): string {
        if ($findCriteria instanceof FindForeign) {
            $findCriteria = $findCriteria->criteria;
        }

        $findCriteria = array_change_key_case($findCriteria, CASE_UPPER);

        $foreignKey = $findCriteria['FOREIGNKEY'] ?? null;
        $reference = $findCriteria['REFERENCE'] ?? null;
        $by = $findCriteria['BY'] ?? null;
        $equals = $findCriteria['EQ'] ?? null;
        $equalsType = gettype($equals);

        $sql = "SELECT * FROM $this->tableName WHERE $foreignKey IN (SELECT $reference.$by FROM $reference WHERE $reference.$by ?)";

        $sql = str_replace(
            "?",
            ($equalsType === "integer" || $equalsType === "double" || $equalsType === "boolean") ? " = $equals ": "LIKE \"$equals\"",
            $sql);

        return $sql;
    }

    /**
     * @param string $sql
     * @return array<T>
     * @throws ReflectionException
     */
    private function query(string $sql): array {
        $stmt = self::_getConnection()->prepare($sql);
        $stmt -> execute();
        $data = $stmt->fetchAll(PDO::FETCH_CLASS);
        $entities = [];

        foreach ($data as $entity) {
            $entities[] = $this->sqlEntityToObject($entity);
        }

        return $entities;
    }

    /**
     * @param object $entity
     * @return object
     * @throws ReflectionException
     */
    private function sqlEntityToObject(object $entity): object {
        $modelReflection = new \ReflectionClass($this->model);
        $modelProperties = $modelReflection->getProperties();

        $entityReflection = new \ReflectionObject($entity);
        $entityProperties = $entityReflection->getProperties();

        $instance = $modelReflection->newInstanceWithoutConstructor();
        $instanceReflection = new \ReflectionObject($instance);

        foreach ($modelProperties as $modelProperty) {
            $field = $modelProperty->getAttributes(self::FIELD)[0] ?? null;
            $primaryKey = $modelProperty->getAttributes(self::ID)[0] ?? null;
            $modelPropertyName = $modelProperty->getName();
            $entityPropertyName = $modelProperty->getName();

            if (($field && empty($field->getArguments())) || ($primaryKey && empty($primaryKey->getArguments()))) {
                $entityPropertyName = $modelProperty->getName();
            } else if ($field && !empty($field->getArguments())) {
                $entityPropertyName = $field->getArguments()[0];
            } else if ($primaryKey && !empty($primaryKey->getArguments())) {
                $entityPropertyName = $primaryKey->getArguments()[0];
            }

            $entityPropertyValue = $entityReflection -> getProperty($entityPropertyName) -> getValue($entity);

            $instanceProperty = $instanceReflection -> getProperty($modelPropertyName);
            $instanceProperty -> setAccessible(true);
            $instanceProperty -> setValue($instance, $entityPropertyValue);
        }

        return $instance;
    }


    /**
     * @param array $criteria
     * @return array<T>
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    public function findMany(array $criteria = []): array {
        $sql = "SELECT * FROM $this->tableName";

        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);
        return ($this->query($sql)) ?: [];
    }

    /**
     * @param array $criteria
     * @return T|null
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    public function findOne(array $criteria = []) {
        $sql = "SELECT * FROM $this->tableName";

        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);
        $sql.= " LIMIT 1";

        $res = $this->query($sql);

        return empty($res) ? null : $res[0];
    }

    /**
     * @param int|string $id
     * @return T|null
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    public function findOneById(int|string $id) {
        $sql = "SELECT * FROM $this->tableName";

        $this->provideCriteria($sql, array(
            $this->primaryKey => $id
        ));

        $res = $this->query($sql);

        return empty($res) ? null : $res[0];
    }

    /**
     * @param array|FindForeign $findCriteria
     * @param array|null $criteria
     * @return bool|array
     */
    public function findOneToManyRelation(array|FindForeign $findCriteria, ?array $criteria = []): bool|array {
        $sql = $this->formatSubQuery($findCriteria);

        return $this->query($sql);
    }

    public function paginate(int $limit, int $offset): array {
        $sql = "SELECT * FROM $this->tableName LIMIT $limit OFFSET $offset";
        return $this->query($sql);
    }

    /**
     * @throws AvocadoRepositoryException|ReflectionException
     */
    public function updateMany(array $updateCriteria, array $criteria = []) {
        if (empty($updateCriteria)) {
            throw new AvocadoRepositoryException(self::EXCEPTION_UPDATE_CRITERIA_MESSAGE);
        }

        $sql = "UPDATE $this->tableName SET ";

        $this->provideUpdateCriteria($sql, $updateCriteria);
        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);

        $this->query($sql);
    }

    /**
     * @throws AvocadoRepositoryException
     */
    public function updateOne(array $updateCriteria, array $criteria = []) {
        if (empty($updateCriteria)) {
            throw new AvocadoRepositoryException(self::EXCEPTION_UPDATE_CRITERIA_MESSAGE);
        }

        $sql = "UPDATE $this->tableName SET ";

        $this->provideUpdateCriteria($sql, $updateCriteria);
        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);

        $sql .= " LIMIT 1";

        $this->query($sql);
    }

    /**
     * @throws AvocadoModelException
     */
    public function updateOneById(array $updateCriteria, string|int $id) {
        if (empty($updateCriteria)) {
            throw new AvocadoModelException(self::EXCEPTION_UPDATE_CRITERIA_MESSAGE);
        }

        $sql = "UPDATE $this->tableName SET ";

        $this->provideUpdateCriteria($sql, $updateCriteria);
        $this->provideCriteria($sql, array(
            $this->primaryKey => $id
        ));

        $this->query($sql);
    }


    /**
     * @param array $criteria
     * @return void
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    public function deleteMany(array $criteria) {
        $sql = "DELETE FROM $this->tableName ";
        $this->provideCriteria($sql, $criteria);

        $this->query($sql);
    }

    /**
     * @param int|string $id
     * @return void
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    public function deleteOneById(int|string $id) {
        $sql = "DELETE FROM $this->tableName ";
        $this->provideCriteria($sql, array(
            $this->primaryKey => $id
        ));

        $this->query($sql);
    }

    /**
     * @param object $object
     * @return string
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    private function getObjectAttributesAsSQLString(object $object): string {
        $ref = new \ReflectionClass($object);
        $output = "";
        $isFirstProperty = true;

        foreach ($ref->getProperties() as $property) {
            $refToProperty = new \ReflectionProperty(get_class($object), $property->getName());
            $isEntityField = !empty($refToProperty->getAttributes(self::FIELD));

            if ($isEntityField) {
                $valueOfProperty = $refToProperty->getValue($object);
                $propertyType = gettype($valueOfProperty);

                if (is_null($valueOfProperty)) {
                    $output .= $isFirstProperty ? " NULL " : ", NULL";
                } else if ($propertyType == "string") {
                    $output .= $isFirstProperty ? " \"$valueOfProperty\" " : " , \"$valueOfProperty\"";
                } else {
                    $output .= $isFirstProperty ? " $valueOfProperty " : " , $valueOfProperty";
                }

                $isFirstProperty = false;
            }
        }

        if ($output == "") {
            throw new AvocadoRepositoryException('Model must have fields if you want save it.');
        }

        return $output;
    }

    /**
     * @param object $object
     * @return string
     */
    private function getInsertColumns(object $object): string {
        $ref = new \ReflectionClass($object);
        $columnStatement = "(";

        foreach ($ref->getProperties() as $property) {
            $propertyName = $property->getName();

            if (!empty($property->getAttributes(self::FIELD))) {
                if(!empty($property->getAttributes(self::FIELD)[0]->getArguments())) {
                    $propertyName = $property->getAttributes(self::FIELD)[0]->getArguments()[0];
                }
            }

            $columnStatement.="$propertyName,";
        }

        if (str_ends_with($columnStatement, ",")) {
            $columnStatement = substr($columnStatement, 0, -1);
        }

        return $columnStatement.")";
    }

    /**
     * @param object $entity
     * @return void
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    public function save(object $entity) {
        $isUserExists = $this->ref->getProperty($this->primaryKey)->isInitialized($entity);

        if($isUserExists) {
            $id = $this->ref->getProperty($this->primaryKey)->getValue($entity);
        }

        $insertColumnStatement = $this->getInsertColumns($entity);
        $sql = "INSERT INTO $this->tableName $insertColumnStatement VALUES (NULL, ";
        $sql.=$this->getObjectAttributesAsSQLString($entity);
        $sql.=")";

        $this->query($sql);
    }

    /**
     * @param ...$entities
     * @return void
     * @throws AvocadoRepositoryException
     * @throws ReflectionException
     */
    public function saveMany(...$entities) {
        $insertColumnStatement = $this->getInsertColumns((object)$entities[0]);
        $sql = "INSERT INTO $this->tableName $insertColumnStatement VALUES ";

        foreach ($entities as $entity) {
            $sql .= "(NULL, ";
            $sql .= $this->getObjectAttributesAsSQLString($entity);
            $sql .= "),";
        }

        $sql = substr($sql, 0, -1);

        $this->query($sql);
    }

    /**
     * @return void
     */
    public function truncate() {
        $this->query("TRUNCATE TABLE $this->tableName");
    }


    /**
     * @param string $to
     * @return void
     */
    public function renameTo(string $to) {
        $this->query("ALTER TABLE $this->tableName RENAME TO $to");
    }
}
