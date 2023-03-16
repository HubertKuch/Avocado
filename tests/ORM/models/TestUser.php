<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Table;

#[Table('users')]
class TestUser {
    #[Id('id')]
    private ?int $id = null;
    #[Field]
    private string $username;
    #[Field]
    private string $password;
    #[Field('amount')]
    private float $amount;
    #[Field]
    private UserRole $role;

    public function __construct(string $username, string $password, float $amount, UserRole $role = UserRole::USER) {
        $this->username = $username;
        $this->password = $password;
        $this->amount = $amount;
        $this->role = $role;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getRole(): UserRole {
        return $this->role;
    }

    public function setRole(UserRole $role): void {
        $this->role = $role;
    }
}
