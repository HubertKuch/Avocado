<?php

namespace Avocado\ORM;

class FindForeign {
    public array $criteria;

    public function __construct() {
        return $this;
    }

    public function reference(string $ref): FindForeign {
        $this->criteria['reference'] = $ref;
        return $this;
    }

    public function by(string $by): FindForeign {
        $this->criteria['by'] = $by;
        return $this;
    }

    public function equals(string|int|float $eq): FindForeign {
        $this->criteria['eq'] = $eq;

        return $this;
    }

    public function key(string $key): FindForeign {
        $this->criteria['foreignKey'] = $key;
        return $this;
    }
}