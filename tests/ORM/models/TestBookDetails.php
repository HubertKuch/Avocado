<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;

#[Table('books')]
class TestBookDetails {
    public function __construct(
        #[Id]
        private int $id,
        #[Field("written_at")]
        private string $writtenAt,
        #[Field("added_at")]
        private string $addedAt
    ) {}

    public function getId(): int {
        return $this->id;
    }

    public function getAddedAt(): string {
        return $this->addedAt;
    }

    public function getWrittenAt(): string {
        return $this->writtenAt;
    }
}