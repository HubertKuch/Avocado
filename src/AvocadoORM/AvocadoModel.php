<?php

namespace Avocado\ORM;

use ReflectionClass;
use ReflectionProperty;
use ReflectionException;

/**
 * @template T
 */
class AvocadoModel extends AvocadoORMSettings {
    const TABLE = __NAMESPACE__."\Attributes\Table";
    const ID = __NAMESPACE__."\Attributes\Id";
    const FIELD = __NAMESPACE__."\Attributes\Field";
    const IGNORE_FIELD_TYPE = __NAMESPACE__."\Attributes\IgnoreFieldType";

    protected string $model;

    public function getModel(): string {
        return $this->model;
    }

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
        $hasProperty = $this->ref->hasProperty($property);

        if (!$hasProperty) {
            foreach ($this->ref->getProperties() as $reflectionProperty) {
                $attr = $reflectionProperty->getAttributes(self::FIELD);
                if (!empty($attr) && !empty($attr[0]->getArguments())) {
                    $hasProperty = ($attr[0] -> getArguments()[0] == $property);
                }
            }
        }

        return $hasProperty;
    }

    protected function isModelPropertyIsType(string $property, string $type): bool {
        if (!$this->ref->hasProperty($property)) {
            $property = $this->getPropertyByAlias($property)->getName();
        }

        $reflectionProperty = new ReflectionProperty($this->model, $property);

        if (!empty($reflectionProperty->getAttributes(self::IGNORE_FIELD_TYPE))) {
            return true;
        }

        $propertyType = $reflectionProperty -> getType() -> getName();

        if ($this->isPropertyIsEnum($reflectionProperty->getName()))
            $propertyType = "object";


        $type = match ($type) {
            "integer" => "int",
            "double" => "float",
            "string" => "string",
            "boolean" => "bool",
            "object" => "object",
            default => "NULL"
        };

        if ($type === "NULL") {
            return $reflectionProperty->getType()->allowsNull();
        }

        return $propertyType === $type;
    }

    protected function getPropertyByAlias(string $alias): ReflectionProperty {
        $targetProperty = null;

        foreach ($this->properties as $property) {
            $propertyFieldAttribute = $property->getAttributes(self::FIELD);
            $propertyFieldAttribute = !empty($propertyFieldAttribute) ? $propertyFieldAttribute[0] : null;

            $propertyIDAttribute = $property->getAttributes(self::ID);
            $propertyIDAttribute = !empty($propertyIDAttribute) ? $propertyIDAttribute[0] : null;

            if ($propertyFieldAttribute && !empty($propertyFieldAttribute->getArguments()) && $propertyFieldAttribute->getArguments()[0] === $alias) {
                $targetProperty = $property;
            } else if ($propertyIDAttribute && !empty($propertyIDAttribute->getArguments()) && $propertyIDAttribute->getArguments()[0] === $alias) {
                $targetProperty = $property;
            }
        }

        return $targetProperty;
    }

    public function isPropertyIsEnum(string $aliasOrName): bool {
        if (!($this->ref->hasProperty($aliasOrName))) {
            $aliasOrName = $this->getPropertyByAlias($aliasOrName)->getName() ?? $aliasOrName;
        }

        try {
            $propertyRef = new ReflectionProperty($this->model, $aliasOrName);

            return enum_exists($propertyRef->getType()->getName());
        } catch (ReflectionException) {
            return false;
        }
    }

    protected function getValueOfEnumProperty(object $object, string $aliasOrName): string|int|float|null {
        if (!($this->ref->hasProperty($aliasOrName))) {
            $aliasOrName = $this->getPropertyByAlias($aliasOrName)->getName() ?? $aliasOrName;
        }

        try {
            $propertyRef = new ReflectionProperty($this->model, $aliasOrName);
            return $propertyRef->getValue($object)?->value;
        } catch (ReflectionException) {
            var_dump("TEST");
            return null;
        }
    }
}
