<?php

namespace Avocado\Tests\Unit;

use Avocado\AvocadoORM\Attributes\Relations\JoinColumn;
use Avocado\AvocadoORM\Attributes\Relations\OneToMany;
use Avocado\AvocadoORM\Attributes\Relations\OneToOne;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;

#[Table('books')]
class TestBook {
    #[Id]
    private int $id;
    private string $name;
    private string $description;
    #[OneToOne]
    #[JoinColumn(table: "book_details", name: "id", referencesTo: "id")]
    private TestBookDetails $details;

    public function __construct(int $id, string $name, string $description, TestBookDetails $details) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->details = $details;
    }


    public function getDetails(): TestBookDetails {
        return $this->details;
    }
}