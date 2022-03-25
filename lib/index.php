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
    private string $field;
    private string $type;

    public function __construct(string $field, string $type)
    {
        $this->field = $field;
        $this->type = $type;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getType(): string
    {
        return $this->type;
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
    private static string $tableName = '';
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
                    $this->fields[] = new Field(
                        $attribute->getArguments()[0] ?? $property->name,
                        $property -> getType()
                    );
                }
            }
        } catch (ReflectionException $e) {}
    }

    private function getTableName(): string{
        return $this->getTableName();
    }

    public static function findAll() {
        $sql = "SELECT * FROM :TABLE_NAME";
        $tableName = call_user_func(array('this', 'getTableName'));
        $stmt = self::_getConnection()->prepare($sql);
        $stmt -> bindParam(':TABLE_NAME', $tableName);
        $stmt -> execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
