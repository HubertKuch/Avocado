<?php

namespace Avocado\AvocadoORM\Attributes\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToOne {
    public function __construct() {}
}