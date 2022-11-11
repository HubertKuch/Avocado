<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\DataSource\DataSource;
use Avocado\DataSource\DataSourceBuilder;
use Avocado\DataSource\Database\DatabaseType;
use Avocado\AvocadoApplication\Attributes\Leaf;
use Avocado\AvocadoApplication\Attributes\Configuration;

#[Configuration]
class MockedDatabaseConfiguration {

    #[Leaf]
    public function getDataSource(): DataSource {
        return (new DataSourceBuilder())
            ->username("root")
            ->password("")
            ->server("127.0.0.1")
            ->port(3306)
            ->databaseType(DatabaseType::MYSQL)
            ->databaseName("")
            ->build();
    }
}
