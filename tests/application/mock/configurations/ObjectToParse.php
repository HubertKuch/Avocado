<?php

namespace Avocado\Tests\Unit\Application;

class ObjectToParse {

    private int $age;
    private string $name;

    public function getAge(): int {
        return $this->age;
    }

    public function getName(): string {
        return $this->name;
    }
}