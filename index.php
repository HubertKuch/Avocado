<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "./lib/index.php";

AvocadoORMSettings::useDatabase('mysql:host=localhost;dbname=blog;', 'root', '');

#[Table('blogs')]
class Blog extends AvocadoORMModel {
    #[Field]
    private int $id;
    #[Field]
    private string $title;

    public function __construct()
    {
        parent::__construct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Blog
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): Blog
    {
        $this->title = $title;
        return $this;
    }
}


