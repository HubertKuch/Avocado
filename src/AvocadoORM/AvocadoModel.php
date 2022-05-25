<?php

namespace Avocado\ORM;

use ReflectionException;
use ReflectionClass;

/**
 * @template T
 */
class AvocadoModel extends AvocadoORMSettings {
    const TABLE = __NAMESPACE__."\Attributes\Table";
    const ID = __NAMESPACE__."\Attributes\Id";
    const IGNORE_FIELD_TYPE = __NAMESPACE__."\Attributes\IgnoreFieldType";

    protected string $model;

    protected ReflectionClass $ref;
    private array $attrs;
    private array $properties;

    protected string $primaryKey;
    protected string $tableName;

    /**
     * @param class-string<T> $model
     */
    public function __construct(string $model) {
        try {
            $this -> model = $model;
            $this -> ref = new ReflectionClass($model);
            $this -> attrs = $this -> ref -> getAttributes();
            $this -> properties = $this -> ref -> getProperties();
            $this -> tableName = $this->getTableName();
            $this -> primaryKey = $this->getPrimaryKey();
        } catch (\Exception $e) {}
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
