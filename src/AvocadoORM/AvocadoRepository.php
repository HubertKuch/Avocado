<?php

namespace Avocado\ORM;

use ReflectionException;

const EXCEPTION_UPDATE_CRITERIA_MESSAGE = "Update criteria don't have to be empty.";
const FIELD = 'Avocado\ORM\Field';

class AvocadoRepository extends AvocadoORMModel implements AvocadoRepositoryActions {
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

    private function query($sql): bool|array {
        $stmt = self::_getConnection()->prepare($sql);
        $stmt -> execute();
        $data = $stmt->fetchAll(\PDO::FETCH_CLASS);
        $entities = [];

        foreach ($data as $entity) {
            $entities[] = $this->sqlEntityToObject($entity);
        }

        return $entities;
    }

    private function sqlEntityToObject(object $entity): object {
        $modelReflection = new \ReflectionClass($this->model);
        $modelProperties = $modelReflection->getProperties();

        $entityReflection = new \ReflectionObject($entity);
        $entityProperties = $entityReflection->getProperties();

        $instance = $modelReflection->newInstanceWithoutConstructor();
        $instanceReflection = new \ReflectionObject($instance);

        foreach ($modelProperties as $modelProperty) {
            $field = $modelProperty->getAttributes(FIELD)[0] ?? null;
            $primaryKey = $modelProperty->getAttributes(ID)[0] ?? null;
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

    public function findMany(array $criteria = []): bool|array {
        $sql = "SELECT * FROM $this->tableName";

        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);
        return $this->query($sql);
    }

    public function findOne(array $criteria = []) {
        $sql = "SELECT * FROM $this->tableName";

        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);
        $sql.= " LIMIT 1";

        $res = $this->query($sql);

        return empty($res) ? null : $res[0];
    }

    public function findOneById($id) {
        $sql = "SELECT * FROM $this->tableName";

        $this->provideCriteria($sql, array(
            $this->primaryKey => $id
        ));

        $res = $this->query($sql);

        return empty($res) ? null : $res[0];
    }

    public function findOneToManyRelation(array|FindForeign $findCriteria, ?array $criteria = []): bool|array {
        $sql = $this->formatSubQuery($findCriteria);

        return $this->query($sql);
    }


    /**
     * @throws AvocadoRepositoryException
     */
    public function updateMany(array $updateCriteria, array $criteria = []) {
        if (empty($updateCriteria)) {
            throw new AvocadoRepositoryException(EXCEPTION_UPDATE_CRITERIA_MESSAGE);
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
            throw new AvocadoRepositoryException(EXCEPTION_UPDATE_CRITERIA_MESSAGE);
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
            throw new AvocadoModelException(EXCEPTION_UPDATE_CRITERIA_MESSAGE);
        }

        $sql = "UPDATE $this->tableName SET ";

        $this->provideUpdateCriteria($sql, $updateCriteria);
        $this->provideCriteria($sql, array(
            $this->primaryKey => $id
        ));

        $this->query($sql);
    }


    public function deleteMany(array $criteria) {
        $sql = "DELETE FROM $this->tableName ";
        $this->provideCriteria($sql, $criteria);

        $this->query($sql);
    }

    public function deleteOneById(int|string $id) {
        $sql = "DELETE FROM $this->tableName ";
        $this->provideCriteria($sql, array(
            $this->primaryKey => $id
        ));

        $this->query($sql);
    }

    private function getObjectAttributesAsSQLString(object $object): string {
        $ref = new \ReflectionClass($object);
        $output = "";
        $isFirstProperty = true;

        foreach ($ref->getProperties() as $property) {
            $refToProperty = new \ReflectionProperty(get_class($object), $property->getName());
            $isEntityField = !empty($refToProperty->getAttributes(FIELD));

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

    private function getInsertColumns(object $object): string {
        $ref = new \ReflectionClass($object);
        $columnStatement = "(";

        foreach ($ref->getProperties() as $property) {
            $propertyName = $property->getName();

            if (!empty($property->getAttributes(FIELD))) {
                if(!empty($property->getAttributes(FIELD)[0]->getArguments())) {
                    $propertyName = $property->getAttributes(FIELD)[0]->getArguments()[0];
                }
            }

            $columnStatement.="$propertyName,";
        }

        if (str_ends_with($columnStatement, ",")) {
            $columnStatement = substr($columnStatement, 0, -1);
        }

        return $columnStatement.")";
    }

    public function save(object $entity) {
        $insertColumnStatement = $this->getInsertColumns($entity);
        $sql = "INSERT INTO $this->tableName $insertColumnStatement VALUES (NULL, ";
        $sql.=$this->getObjectAttributesAsSQLString($entity);
        $sql.=")";

        $this->query($sql);
    }

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

    public function truncate() {
        $this->query("TRUNCATE TABLE $this->tableName");
    }


    public function renameTo(string $to) {
        $this->query("ALTER TABLE $this->tableName RENAME TO $to");
    }
}
