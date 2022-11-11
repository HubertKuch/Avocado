<?php

namespace Avocado\ORM\Attributes;

#[\Attribute]
class Table {
    private string $table;

    public function __construct(string $name) {
        $this->table = $name;
    }

    /**
     * @return string
     */
    public function getTable(): string {
        return $this->table;
    }
}
