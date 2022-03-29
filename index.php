<?php

require "./vendor/autoload.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Avocado\ORM\AvocadoORMSettings;
use Avocado\ORM\AvocadoRepository;
use Avocado\ORM\Field;
use Avocado\ORM\Id;
use Avocado\ORM\Table;
use Avocado\Router\AvocadoRouter;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;

AvocadoORMSettings::useDatabase('mysql:host=localhost;dbname=notebook', 'root', '');
AvocadoORMSettings::useFetchOption(PDO::FETCH_ASSOC);
AvocadoRouter::useJSON();


#[Table('users')]
class User {
    #[Id('id')]
    private int $id;
    #[Field]
    private string $username;
    #[Field]
    private string $password;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
}

$usersRepository = new AvocadoRepository(User::class);

AvocadoRouter::GET('/api/v1/users', [], function(AvocadoRequest $req, AvocadoResponse $res) use ($usersRepository) {
    $users = $usersRepository -> findMany();
    $res -> json($users) -> withStatus(200);
});

AvocadoRouter::listen();
