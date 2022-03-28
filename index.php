<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "./lib/index.php";

AvocadoORMSettings::useDatabase('mysql:host=localhost;dbname=egzamin;', 'root', '');

#[Table('ryby')]
class Blog {
    #[Field]
    private int $id;
    #[Field]
    private string $title;
}

$blogRepository = new AvocadoRepository(Blog::class);

var_dump($blogRepository->findMany());