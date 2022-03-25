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
    private ReflectionClass $childRef;
    private static string $tableName;
    private static Id $primaryKey;
    private static array $fields = [];

    public function __construct() {
        $child = get_class($this);
        try {
            $this->childRef = new ReflectionClass($child);

            // GET TABLE NAME
            foreach ($this->childRef->getAttributes() as $attribute) {
                $args = $attribute->getArguments();

                if ($attribute->getName() == 'Table') {
                    self::$tableName = $args[0] ?? $child;
                }
            }

            // GET FIELDS
            $properties = $this->childRef->getProperties();

            foreach ($properties as $property) {
                $attributes = $property->getAttributes();

                foreach ($attributes as $attribute) {
                    $nameAttr = $attribute->getName();

                    if ($nameAttr == 'Id') {
                        self::$primaryKey= new Id(
                            $attribute->getArguments()[0] ?? $property->name,
                            $property -> getType()
                        );
                    } else if ($nameAttr == 'Field') {
                        self::$fields[] = new Field(
                            $attribute->getArguments()[0] ?? $property->name,
                            $property -> getType()   
                        );
                    }
                    
                }
            }
        } catch (ReflectionException $e) {}
    }

    public static function getTableName(): string{
        return self::$tableName;
    }

    public static function findAll() {
        $sql = "SELECT * FROM :table";
        $stmt = self::_getConnection()->prepare($sql);
        $table = strval(self::$tableName);

        $stmt -> execute(array(
            ":table" => $table
        ));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
