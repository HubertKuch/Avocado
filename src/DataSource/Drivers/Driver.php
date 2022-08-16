<?php

namespace Avocado\DataSource\Drivers;

use Avocado\DataSource\Drivers\Connection\Connection;

interface Driver {
    public function connect(): Connection;
}
