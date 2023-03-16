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
    #[JoinColumn(name: 'user_id')]
    #[OneToMany(joinedTable: "books", class: TestBook::class)]
    private array $books;

    public function getBooks(): array {
        return $this->books;
    }
}