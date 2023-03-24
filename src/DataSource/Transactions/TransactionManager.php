<?php

namespace Avocado\DataSource\Transactions;

interface TransactionManager {
    public function begin();
    public function commit();
    public function rollback();
}