<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "./lib/index.php";

AvocadoORMSettings::useDatabase('mysql:host=localhost;dbname=blog;', 'root', '');

#[Table('blogs')]
class Blog {
    #[Id]
    private string $id;
}

$blogRepository = new AvocadoRepository(Blog::class);

print_r(
    $blogRepository -> findOneById(3)
);