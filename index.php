<?php

require "./vendor/autoload.php";
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

use Avocado\ORM\AvocadoORMSettings;
use Avocado\ORM\AvocadoRepository;
use Avocado\ORM\Field;
use Avocado\ORM\Id;
use Avocado\ORM\Table;
use Avocado\Router\AvocadoRouter;
use Avocado\Router\Request;
use Avocado\Router\Response;

AvocadoORMSettings::useDatabase('mysql:host=localhost;dbname=egzamin', 'root', '');
AvocadoORMSettings::useFetchOption(PDO::FETCH_ASSOC);
AvocadoRouter::useJSON();

#[Table('uzytkownik')]
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

AvocadoRouter::GET('/api/v1/users', [], function(Request $req, Response $res) use ($usersRepository) {
    $users = $usersRepository -> findMany();
    $res -> json($users) -> withStatus(200);
});

AvocadoRouter::POST('/api/v1/users', [], function(Request $req, Response $res) use ($usersRepository) {
    $username = $req->body['username'] ?? null;
    $password = $req->body['password'] ?? null;

    $user = new User($username, $password);

    $usersRepository -> save($user);
});

AvocadoRouter::listen();
