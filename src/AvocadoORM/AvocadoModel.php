<?php

namespace Avocado\ORM;

use Avocado\AvocadoORM\Attributes\Relations\JoinColumn;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\Arrays;
use Exception;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class AvocadoModel extends AvocadoORMSettings {
    const TABLE = __NAMESPACE__ . "\Attributes\Table";
    const ID = __NAMESPACE__ . "\Attributes\Id";
    const FIELD = __NAMESPACE__ . "\Attributes\Field";
    const IGNORE_FIELD_TYPE = __NAMESPACE__ . "\Attributes\IgnoreFieldType";

    /**
     * @param class-string<T> $model
     * */
    protected string $model;

    public function getModel(): string {
        return $this->model;
    }

    protected ReflectionClass $ref;
    /**
     * @var ReflectionAttribute[] $properties
     * */
    private array $attrs;
    /**
     * @var ReflectionProperty[] $properties
     * */
    private array $properties;
    protected string $primaryKey;
    protected string $tableName;

    public function __construct(string $model) {
        try {
            $this->model = $model;
            $this->ref = new ReflectionClass($model);
            $this->attrs = $this->ref->getAttributes();
            $this->properties = $this->ref->getProperties();
            $this->tableName = $this->getTableName();
            $this->primaryKey = $this->getPrimaryKey();
        } catch (Exception $e) {
        }
    }

    /**
     * @throws TableNameException
     */
    private function getTableName() {
        $tableName = '';

        foreach ($this->attrs as $attr) {
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

    private function getPrimaryKey() {
        $attr = null;
        $propertyTarget = null;

        foreach ($this->properties as $property) {
            $ref = new ReflectionProperty($this->model, $property->getName());
            $idAttr = $ref->getAttributes(self::ID);

            if (!empty($idAttr)) {
                if (count($idAttr) > 1) {
                    throw new AvocadoModelException(sprintf("Primary key must one for model. %s has %s primary keys.",
                        $this->model,
                        count($idAttr)));
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
                    $hasProperty = ($attr[0]->getArguments()[0] == $property);
                }
            }
        }

        return $hasProperty;
    }

    protected function getPropertyByNameOrAlias(string $nameOrAlias): ?ReflectionProperty {
        if (!$this->ref->hasProperty($nameOrAlias)) {
            return $this->getPropertyByAlias($nameOrAlias);
        } else {
            return $this->getPropertyByName($nameOrAlias);
        }
    }

    protected function isModelPropertyIsType(string $property, string $type): bool {
        try {
            if (!$this->ref->hasProperty($property)) {
                $property = $this->getPropertyByAlias($property)->getName();
            }

            $reflectionProperty = new ReflectionProperty($this->model, $property);

            if (!empty($reflectionProperty->getAttributes(self::IGNORE_FIELD_TYPE))) {
                return true;
            }

            $propertyType = $reflectionProperty->getType()->getName();

            if ($this->isPropertyIsEnum($reflectionProperty->getName())) $propertyType = "object";


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
        } catch (Exception) {
            return false;
        }
    }

    protected function getPropertyByAlias(string $alias): ReflectionProperty {
        return Arrays::find($this->properties, function($property) use ($alias) {
            return  AnnotationUtils::getInstance($property, Field::class)?->getField() === $alias ||
                    AnnotationUtils::getInstance($property, Id::class)?->getField() === $alias;
        });
    }

    protected function getPropertyByName(string $name): ReflectionProperty {
        return Arrays::find($this->properties, function($property) use ($name) {
            return $property->getName() === $name;
        });
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
            return null;
        }
    }

    /**
     * @return ReflectionProperty[]
     * */
    protected function getJoinedProperties(string $relationAnnotation = null): array {
        if ($relationAnnotation === null) {
            return array_filter($this->properties,
                fn($property) => AnnotationUtils::isAnnotated($property, JoinColumn::class));
        }

        $properties = array_filter($this->properties,
            fn($property) => AnnotationUtils::isAnnotated($property, JoinColumn::class));

        return array_filter($properties, fn($property) => AnnotationUtils::isAnnotated($property, $relationAnnotation));
    }
}
