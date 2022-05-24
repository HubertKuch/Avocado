<?php

namespace Avocado\ORM;

use ReflectionException;


class AvocadoORMModel extends AvocadoORMSettings {
    const TABLE = __NAMESPACE__."\Attributes\Table";
    const ID = __NAMESPACE__."\Attributes\Id";
    const IGNORE_FIELD_TYPE = __NAMESPACE__."\Attributes\IgnoreFieldType";

    protected string $model;

    private \ReflectionClass $ref;
    private array $attrs;
    private array $properties;

    protected string $primaryKey;
    protected string $tableName;

    public function __construct($model) {
        if (!is_string($model)) {
            throw new \TypeError(sprintf("Model must be string who referent to class definition, passed %s", gettype($model)));
        }

        $this -> model = $model;
        $this -> ref = new \ReflectionClass($model);
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
            if ($attr->getName() == self::TABLE) {
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
        $attr = null;
        $propertyTarget = null;

        foreach($this->properties as $property) {
            $ref = new \ReflectionProperty($this->model, $property->getName());
            $idAttr = $ref->getAttributes(self::ID);

            if (!empty($idAttr)) {
                if (count($idAttr) > 1) {
                    throw new AvocadoModelException(sprintf("Primary key must one for model. %s has %s primary keys.", $this->model, count($idAttr)));
                }

                $attr = $idAttr[0];
                $propertyTarget = $property;
            }
        }

        if (!$attr) {
            throw new AvocadoModelException("Missing primary key on $this->model model.");
        }

        if (!empty($attr->getArguments())) {
            return $attr->getArguments()[0];
        }

        return $propertyTarget->getName();
    }

    protected function isModelHasProperty(string $property): bool {
        try {
            new \ReflectionProperty($this->model, $property);
            return true;
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * @throws ReflectionException
     */
    protected function isModelPropertyIsType(string $property, string $type): bool {
        $reflectionProperty = new \ReflectionProperty($this->model, $property);

        if (!empty($reflectionProperty->getAttributes(self::IGNORE_FIELD_TYPE))) {
            return true;
        }

        $propertyType = $reflectionProperty -> getType()->getName();
        $type = match ($type) {
            "integer" => "int",
            "double" => "float",
            "string" => "string",
            "boolean" => "bool",
            default => "null"
        };

        return $propertyType === $type;
    }
}
