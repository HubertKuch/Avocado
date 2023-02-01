<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\DataSource\DataSource;
use Avocado\DataSource\DataSourceBuilder;
use Avocado\AvocadoApplication\Attributes\Leaf;
use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\DataSource\Exceptions\CannotBuildDataSourceException;
use Avocado\MysqlDriver\MySQLDriver;

#[Configuration]
class MockedDatabaseConfiguration {

    /**
     * @throws CannotBuildDataSourceException
     */
    #[Leaf]
    public function getDataSource(): DataSource {
        return (new DataSourceBuilder())
            ->username("user")
            ->password("user")
            ->server("172.17.0.2")
            ->port(3306)
            ->driver(MySQLDriver::class)
            ->databaseName("")
            ->build();
    }
}
