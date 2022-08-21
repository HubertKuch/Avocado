<?php

namespace Avocado\DataSource;

use Avocado\DataSource\Database\DatabaseType;
use Avocado\DataSource\Drivers\MySQLDriver;

class DataSourceBuilder {
    private string $url;
    private string $server;
    private string $username;
    private string $password;
    private DatabaseType $databaseType;
    private string $database;
    private int $port;

    public function url(string $url): DataSourceBuilder {
        $this->url = $url;

        return $this;
    }

    public function databaseType(DatabaseType $databaseType): DataSourceBuilder {
        $this->databaseType = $databaseType;

        return $this;
    }

    public function databaseName(string $database): DataSourceBuilder {
        $this->database = $database;

        return $this;
    }

    public function username(string $username): DataSourceBuilder {
        $this->username = $username;

        return $this;
    }

    public function password(string $password): DataSourceBuilder {
        $this->password = $password;

        return $this;
    }

    public function port(int $port): DataSourceBuilder {
        $this->port = $port;

        return $this;
    }

    public function server(string $server): DataSourceBuilder {
        $this->server = $server;

        return $this;
    }

    public function build(): DataSource {
        $driver = match ($this->databaseType) {
            DatabaseType::MYSQL => new MySQLDriver(
                $this->username,
                $this->password,
                $this->server,
                $this->database,
                $this->port,
            )
        };

        return new DataSource($driver);
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getServer(): string {
        return $this->server;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getPort(): int {
        return $this->port;
    }
}
