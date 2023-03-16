<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;

#[Table('books')]
class TestBook {
    #[Id]
    private int $id;
}