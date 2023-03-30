<?php

namespace Avocado\Tests\Unit;

use Avocado\AvocadoORM\Attributes\Relations\JoinColumn;
use Avocado\AvocadoORM\Attributes\Relations\ManyToOne;
use Avocado\AvocadoORM\Attributes\Relations\OneToMany;
use Avocado\AvocadoORM\Attributes\Relations\OneToOne;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;

#[Table('books')]
class TestBookWithManyToOneRelation {
    #[Id]
    private int $id;
    #[Field]
    private string $name;
    #[Field]
    private string $description;
    #[OneToOne]
    #[JoinColumn(table: "book_details", name: "id", referencesTo: "id")]
    private TestBookDetails $details;
    #[ManyToOne]
    #[JoinColumn(table: "users", name: "user_id", referencesTo: "id")]
    private TestUser $user;

    public function __construct(int $id, string $name, string $description, TestBookDetails $details, TestUser $user) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->details = $details;
        $this->user = $user;
    }


    public function getDetails(): TestBookDetails {
        return $this->details;
    }

    public function getUser(): TestUser {
        return $this->user;
    }
}