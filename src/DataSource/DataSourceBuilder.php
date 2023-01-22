<?php

namespace Avocado\DataSource;

use Avocado\DataSource\Drivers\Driver;
use Avocado\DataSource\Drivers\MySQLDriver;
use Avocado\DataSource\Exceptions\CannotBuildDataSourceException;
use Avocado\Utils\ReflectionUtils;

class DataSourceBuilder {
    private string $url;
    private string $server;
    private string $username;
    private string $password;
    private string $driverClassName;
    private string $database;
    private int $port;
    private string $charset = 'utf8';

    public function url(string $url): DataSourceBuilder {
        $this->url = $url;

        return $this;
    }

    public function charset(string $charset): DataSourceBuilder {
        $this->charset = $charset;

        return $this;
    }

    public function driver(string $driverClassName): DataSourceBuilder {
        $this->driverClassName = $driverClassName;

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

    /**
     * @throws CannotBuildDataSourceException
     */
    public function build(): DataSource {
        if (ReflectionUtils::implements($this->driverClassName, Driver::class)) {
            $instance = ReflectionUtils::instance($this->driverClassName, [
                $this->username,
                $this->password,
                $this->server,
                $this->database,
                $this->port,
                $this->charset
            ]);

            return new DataSource($instance);
        }

        throw new CannotBuildDataSourceException("Invalid driver class name");
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

    public function getCharset(): string {
        return $this->charset;
    }
}
