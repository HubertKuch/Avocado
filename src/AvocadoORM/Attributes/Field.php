<?php

namespace Avocado\ORM\Attributes;

use Attribute;

#[Attribute]
class Field {
    protected string $field;
    protected string $type;
    protected string $constraint;

    public function __construct(string $field = "", string $type = "") {
        $this->field = $field;
        $this->type = $type;
    }

    public function getField(): string {
        return $this->field;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getConstraint(): string {
        return $this->constraint;
    }
}
