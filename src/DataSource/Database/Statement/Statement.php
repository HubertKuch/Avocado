<?php

namespace Avocado\DataSource\Database\Statement;

use Avocado\AvocadoORM\Mappers\Mapper;

interface Statement {
    public function execute(): array;
    public function mapper(): Mapper;
}
