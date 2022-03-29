<?php

require "./vendor/autoload.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Avocado\ORM\AvocadoORMSettings;
use Avocado\ORM\AvocadoRepository;
use Avocado\ORM\Id;
use Avocado\ORM\Table;
use Avocado\ORM\FindForeign;
use Avocado\Router\AvocadoRouter;
use Avocado\Router\AvocadoRequest;
use Avocado\Router\AvocadoResponse;

AvocadoORMSettings::useDatabase('mysql:host=localhost;dbname=notebook', 'root', '');
AvocadoORMSettings::useFetchOption(PDO::FETCH_ASSOC);
AvocadoRouter::useJSON();

#[Table('notes')]
class Note {
    #[Id('id')]
    private int $id;
}

$notesRepository = new AvocadoRepository(Note::class);

AvocadoRouter::GET('/api/v1/users', [], function(AvocadoRequest $req, AvocadoResponse $res) use ($notesRepository) {
    $findCriteria = new FindForeign();
    $findCriteria -> key("group_id") -> reference("groups") -> by("id") -> equals(2);

    $usersNotes = $notesRepository -> findOneToManyRelation($findCriteria);

    $res -> json($usersNotes) -> withStatus(200);
});

AvocadoRouter::listen();
