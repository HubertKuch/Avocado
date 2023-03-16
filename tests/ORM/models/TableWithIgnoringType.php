<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\IgnoreFieldType;
use Avocado\ORM\Attributes\Table;

#[Table('users')]
class TableWithIgnoringType {
    #[Id]
    private ?int $id;
    #[Field]
    private UserRole $role;

    public function __construct(?int $id, UserRole $role) {
        $this->id = $id;
        $this->role = $role;
    }

    public function getRole(): UserRole {
        return $this->role;
    }
}
