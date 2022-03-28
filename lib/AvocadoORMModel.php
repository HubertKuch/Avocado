<?php

require "AvocadoORMSettings.php";

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