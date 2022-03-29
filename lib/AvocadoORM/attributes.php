<?php

namespace Avocado\ORM;

// ATTRIBUTES
#[\Attribute]
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

#[\Attribute]
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

#[\Attribute]
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