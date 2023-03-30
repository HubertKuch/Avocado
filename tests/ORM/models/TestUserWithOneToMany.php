<?php

namespace Avocado\Tests\Unit;

use Avocado\AvocadoORM\Attributes\Relations\JoinColumn;
use Avocado\AvocadoORM\Attributes\Relations\OneToMany;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;

#[Table('users')]
class TestUserWithOneToMany {
    #[Id]
    private int $id;
    #[Field]
    private string $username;
    #[Field]
    private string $password;
    #[Field]
    private float $amount;
    #[OneToMany(class: TestBook::class)]
    #[JoinColumn(table: "books", name: 'user_id', referencesTo: 'id')]
    private array $books;

    public function __construct(int $id, string $username, string $password, array $books) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->books = $books;
        $this->amount = 0;
    }


    public function getBooks(): array {
        return $this->books;
    }
}