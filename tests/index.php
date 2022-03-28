<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../lib/index.php";

AvocadoORMSettings::useDatabase('mysql:host=localhost;dbname=blog;', 'root', '');
AvocadoORMSettings::useFetchOption(PDO::FETCH_ASSOC);

#[Table('users')]
class User{
    #[Id]
    private int $id;
    #[Field]
    private string $username;
    #[Field]
    private string $password;

    public function __construct(string $username, string $password) {
        $this -> password = $password;
        $this -> username = $username;
    }
}

$usersRepository = new AvocadoRepository(User::class);
