<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Table;

#[Table('table2')]
class TestModelWithoutPassedId {
    #[Id]
    private int $id;
    #[Field]
    private string $field;
}
