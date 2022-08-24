<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Leaf;
use Avocado\DataSource\Database\DatabaseType;
use Avocado\DataSource\DataSource;
use Avocado\DataSource\DataSourceBuilder;

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
