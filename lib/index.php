<?php

// UTILS

function endsWith(string $string, string $criteria) {
    if (strlen($string) == 0) return true;
    return (substr($string, -strlen($string)) === $criteria);
}

// ATTRIBUTES

#[Attribute]
class Table {
    private string $table;
    public function __construct(string $name) {
        $this ->table = $name;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }
}


#[Attribute]
class Field {
    protected string $field;
    protected string $type;
    protected string $constraint;

    public function __construct(string $field, string $type)
    {
        $this->field = $field;
        $this->type = $type;
    }

    protected function getField(): string
    {
        return $this->field;
    }

    protected function getType(): string
    {
        return $this->type;
    }

    protected function getConstraint(): string {
        return $this->constraint;
    }
}

#[Attribute]
class Id {
    protected string $field;
    protected string $type;
    protected string $constraint;

    public function __construct(string $field, string $type)
    {
        $this->field = $field;
        $this->type = $type;
    }

    protected function getField(): string
    {
        return $this->field;
    }

    protected function getType(): string
    {
        return $this->type;
    }

    protected function getConstraint(): string {
        return $this->constraint;
    }
}

class AvocadoORMSettings {
    private static array $settings;

    public static function useDatabase(string $dsn, string $user, string $pass) {
        self::$settings['CONNECTION'] = new \PDO($dsn, $user, $pass);
    }

    protected static function _getConnection(): PDO {
        return self::$settings['CONNECTION'];
    }
}

class AvocadoORMModel extends AvocadoORMSettings {
    private string $model;
    private ReflectionClass $ref;
    private array $attrs;

    public function __construct($model) {
        if (!is_string($model)) {
            throw new TypeError(sprintf("Model must be string who referent to class definition, passed %s", gettype($model)));
        }

        $this -> model = $model;
        $this -> ref = new ReflectionClass($model);
        $this -> attrs = $this -> ref -> getAttributes();
    }

    /**
     * @throws TableNameException
     */
    protected function getTableName() {
        $tableName = '';

        foreach($this->attrs as $attr) {
            if ($attr->getName() == "Table") {
                $val = $attr->getArguments();
                if (!empty($val)) {
                    $tableName = $val[0];
                } else {
                    $tableName = $this->model;
                }

            } else {
                throw new TableNameException("Table name must be provided to model");
            }
        }

        return $tableName;
    }
}

interface AvocadoRepositoryMethods {
    public function findMany(array $criteria);
    public function findOne(array $criteria);
}

class AvocadoRepository extends AvocadoORMModel implements AvocadoRepositoryMethods {
    private string $tableName;
    public function __construct($model) {
        parent::__construct($model);
        $this->tableName = $this->getTableName();
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

    /**
     * @throws TableNameException
     */
    public function findMany(array $criteria = []) {
        $sql = "SELECT * FROM $this->tableName";

        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);


        $stmt = self::_getConnection()->prepare($sql);
        $stmt -> execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findOne(array $criteria = []) {
        $sql = "SELECT * FROM $this->tableName";

        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);
        $sql.= " LIMIT 1";

        $stmt = self::_getConnection()->prepare($sql);
        $stmt -> execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
