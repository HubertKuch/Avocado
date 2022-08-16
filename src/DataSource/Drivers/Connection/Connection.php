<?php

namespace Avocado\DataSource\Drivers\Connection;


use Avocado\DataSource\Database\Statement\Statement;

interface Connection {
    public function prepare(string $sql): Statement;
    public function close(): void;
}
