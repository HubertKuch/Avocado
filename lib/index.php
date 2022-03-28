<?php

/**
 * SETTINGS: { CONNECTION }
 * */

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

class AvocadoRepository extends AvocadoORMModel {
    public function __construct($model) {
        parent::__construct($model);
    }

    public function findMany() {
        $table = $this->getTableName();
        $sql = "SELECT * FROM $table";
        $stmt = self::_getConnection()->prepare($sql);
        $stmt -> execute();


        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
