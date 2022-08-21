<?php

namespace Avocado\DataSource\Builder;

class MySQLQueryBuilder implements SQLBuilder {

    public function find(string $tableName, array $criteria, ?array $special = []): string {
        $base = "SELECT * FROM $tableName";

        if (!empty($criteria)) {
            $base .= " WHERE ".$this->buildCriteria($criteria);
        }

        return $base;
    }

    public function update(string $tableName, array $updateCriteria, array $findCriteria = []): string {
        $base = "UPDATE $tableName SET ";

        $base .= $this->buildUpdateCriteria($updateCriteria);
        $base .= $this->buildCriteria($findCriteria);

        return $base;
    }

    public function delete(string $tableName, array $criteria): string {
       $base = "DELETE FROM $tableName ";

       $base .= $this->buildCriteria($criteria);

       return $base;
    }

    public function save(string $tableName, object $object): string {
        return "";
    }

    public function buildCriteria(array $criteria): string {
        $sql = "";

        foreach ($criteria as $key => $value) {
            $valueType = gettype($value);

            if (is_object($value)) {
                $valueType = $value->value ?? NULL;
            }

            if ($valueType === "integer" || $valueType === "double" || $valueType === "boolean") $sql.=" $key = $value AND ";
            else if ($valueType == "NULL") $sql .= " $key = null AND";
            else if ($valueType === "string") $sql.= " $key LIKE \"$value\" AND";
        }

        return substr($sql, 0,-4);
    }

    public function buildUpdateCriteria(array $criteria): string {
        $sql = "";

        foreach ($criteria as $key => $value) {
            $valueType = gettype($value);

            if (is_object($value)) {
                $valueType = $value->value ?? NULL;
            }

            if ($valueType === "integer" || $valueType === "double" || $valueType === "boolean") $sql.=" $key = $value, ";
            else if ($valueType == "NULL") $sql .= " $key = null ";
            else if ($valueType === "string") $sql.= " $key = \"$value\" , ";
        }

        return substr($sql, 0, -2);
    }
}
