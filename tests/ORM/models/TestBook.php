<?php

namespace Avocado\Tests\Unit;

use Avocado\AvocadoORM\Attributes\Relations\JoinColumn;
use Avocado\AvocadoORM\Attributes\Relations\OneToMany;
use Avocado\AvocadoORM\Attributes\Relations\OneToOne;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;

#[Table('books')]
class TestBook {
    #[Id]
    private int $id;
    #[Field]
    private string $name;
    #[Field]
    private string $description;
    #[OneToOne]
    #[JoinColumn(table: "book_details", name: "id", referencesTo: "id")]
    private TestBookDetails $details;
    #[Field("user_id")]
    private int $userId;

    public function __construct(int $id, string $name, string $description, TestBookDetails $details, int $userId) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->details = $details;
        $this->userId = $userId;
    }


    public function getDetails(): TestBookDetails {
        return $this->details;
    }
}