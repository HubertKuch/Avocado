<?php

namespace Avocado\DataSource\Transactions;

interface TransactionManager {
    public function begin(): bool;
    public function commit(): bool;
    public function rollback(): bool;
}