<?php

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
    private static array $settings = array(
        "FETCH_OPTION" => 2,
        "CONNECTION" => null
    );

    public static function useDatabase(string $dsn, string $user, string $pass) {
        self::$settings['CONNECTION'] = new \PDO($dsn, $user, $pass);
    }

    public static function useFetchOption(int $option) {
        self::$settings['FETCH_OPTION'] = $option;
    }

    protected static function _getConnection(): PDO {
        return self::$settings['CONNECTION'];
    }

    protected static function _getFetchOption(): int {
        return self::$settings['FETCH_OPTION'];
    }
}

class AvocadoORMModel extends AvocadoORMSettings {
    private string $model;
    private ReflectionClass $ref;
    private array $attrs;
    private array $properties;

    protected string $primaryKey;
    protected string $tableName;

    public function __construct($model) {
        if (!is_string($model)) {
            throw new TypeError(sprintf("Model must be string who referent to class definition, passed %s", gettype($model)));
        }

        $this -> model = $model;
        $this -> ref = new ReflectionClass($model);
        $this -> attrs = $this -> ref -> getAttributes();
        $this -> properties = $this -> ref -> getProperties();
        $this -> tableName = $this->getTableName();
        $this -> primaryKey = $this->getPrimaryKey();
    }

    /**
     * @throws TableNameException
     */
    private function getTableName() {
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

    /**
     * @throws ReflectionException
     * @throws AvocadoModelException
     */
    private function getPrimaryKey() {
        $prop = null;

        foreach($this->properties as $property) {
            $ref = new ReflectionProperty($this->model, $property->getName());
            $idProp = $ref->getAttributes('Id');

            if (!empty($idProp)) {
                if (count($idProp) > 1) {
                    throw new AvocadoModelException(sprintf("Primary key must one for model. %s has %s primary keys.", $this->model, count($idProp)));
                }

                $prop = $idProp[0];
            }
        }

        if (!$prop) {
            throw new AvocadoModelException("Missing primary key on $this->model model.");
        }

        if (!empty($prop->getArguments())) {
            return $prop->getArguments()[0];
        }

        return $prop -> getName();
    }
}

interface AvocadoRepositoryMethods {
    public function findMany(array $criteria);
    public function findOne(array $criteria);
    public function findOneById(string|int $id);
}

class AvocadoRepository extends AvocadoORMModel implements AvocadoRepositoryMethods {
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

    /**
     * @throws TableNameException
     */

    private function query($sql) {
        $stmt = self::_getConnection()->prepare($sql);
        $stmt -> execute();

        return $stmt->fetchAll(self::_getFetchOption());
    }

    /**
     * @throws TableNameException
     */
    public function findMany(array $criteria = []) {
        $sql = "SELECT * FROM $this->tableName";

        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);
        return $this->query($sql);
    }

    /**
     * @throws TableNameException
     */
    public function findOne(array $criteria = []) {
        $sql = "SELECT * FROM $this->tableName";

        if (!empty($criteria)) $this->provideCriteria($sql, $criteria);
        $sql.= " LIMIT 1";

        $res = $this->query($sql);

        return empty($res) ? null : $res[0];
    }

    /**
     * @throws TableNameException
     */
    public function findOneById($id) {
        $sql = "SELECT * FROM $this->tableName";

        $this->provideCriteria($sql, array(
            $this->primaryKey => $id
        ));

        $res = $this->query($sql);

        return empty($res) ? null : $res[0];
    }
}
