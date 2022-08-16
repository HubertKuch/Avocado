<?php

namespace Avocado\DataSource\Database\Statement;

interface Statement {
    public function execute(): array;
}
