<?php

namespace Avocado\ORM;

const EXCEPTION_UPDATE_CRITERIA_MESSAGE = "Update criteria don't have to be empty.";


class AvocadoRepository extends AvocadoORMModel implements AvocadoRepositoryActions {
    public function __construct($model) {
        parent::__construct($model);
    }

    private function provideCriteria(string &$sql, array $criteria): void {
        $sql.= " WHERE ";
        foreach ($criteria as $key => $value) {
            $valueType = gettype($value);

            if ($valueType === "integer" || $valueType === "boolean") $sql.=" $key = $value AND ";
            else if ($valueType === "string") $sql.= " $key LIKE \"$value\" AND";
        }

        $sql = substr($sql, 0,-4);
    }

    private function provideUpdateCriteria(string &$sql, array $criteria): void {
        foreach ($criteria as $key => $value) {
            $valueType = gettype($value);

            if ($valueType === "integer" || $valueType === "boolean") $sql.=" $key = $value, ";
            else if ($valueType === "string") $sql.= " $key = \"$value\" , ";
        }

        $sql = substr($sql, 0, -2);
    }

    private function query($sql) {
        $stmt = self::_getConnection()->prepare($sql);
        $stmt -> execute();

        return $stmt->fetchAll(self::_getFetchOption());
    }


    public function findMany(array $criteria = []) {
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
            $isEntityField = !empty($refToProperty->getAttributes('Field'));

            if ($isEntityField) {
                $valueOfProperty = $refToProperty->getValue($object);
                $propertyType = gettype($valueOfProperty);

                if ($propertyType == "string") {
                    $output .= $isFirstProperty ? " \"$valueOfProperty\" " : " , \"$valueOfProperty\"";
                } else {
                    $output .= $isFirstProperty ? " $valueOfProperty " : " , $valueOfProperty";
                }

                $isFirstProperty = false;

            }
        }

        return $output;
    }

    public function save(object $entity) {
        $sql = "INSERT INTO $this->tableName VALUES (NULL, ";
        $sql.=$this->getObjectAttributesAsSQLString($entity);
        $sql.=")";

        $this->query($sql);
    }

    public function saveMany(...$entities) {
        $sql = "INSERT INTO $this->tableName VALUES ";

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