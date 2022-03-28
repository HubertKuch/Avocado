<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "../lib/AvocadoRepository.php";

AvocadoORMSettings::useDatabase('mysql:host=localhost;dbname=blog', 'root', '');
AvocadoORMSettings::useFetchOption(PDO::FETCH_ASSOC);

#[Table('user')]
class User {
    #[Id]
    private int $id;
    #[Field]
    private string $username;
    #[Field]
    private string $password;

    public function __construct(string $username, string $password) {
        $this->username = $username;
        $this->password = $password;
    }


}

#[Table('blogs')]
class Blog {
    #[Id]
    private int $id;
    #[Field]
    private string $title;
    #[Field]
    private string $description;
    #[Field]
    private int $user_id;
    #[Field]
    private string $slug;
    #[Field]
    private string $img_src;

    public function __construct(string $title, string $description, int $user_id, string $slug, string $img_src) {
        $this->title = $title;
        $this->description = $description;
        $this->user_id = $user_id;
        $this->slug = $slug;
        $this->img_src = $img_src;
    }
}

$userRepo = new AvocadoRepository(User::class);
$blogsRepo = new AvocadoRepository(Blog::class);
