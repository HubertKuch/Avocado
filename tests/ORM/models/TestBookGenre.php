<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;

#[Table("genre")]
class TestBookGenre {
    #[Id]
    private string $id;
    #[Field]
    private string $name;

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }
}