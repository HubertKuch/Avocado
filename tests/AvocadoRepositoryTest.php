<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Table;
use Avocado\ORM\Attributes\Id;
use PHPUnit\Framework\TestCase;
use Avocado\ORM\AvocadoRepository;
use Avocado\ORM\AvocadoORMSettings;

const DATABASE = "avocado_test_db";
const DSN = "mysql:host=127.0.0.1;dbname=".DATABASE;
const USER = "root";
const PASSWORD = "";

#[Table('users')]
class TestUser {
    #[Id]
    private int $id;
    #[Field]
    private string $username;
    #[Field]
    private string $password;
    #[Field('amount')]
    private float $amount;

    public function __construct(string $username, string $password, float $amount) {
        $this->username = $username;
        $this->password = $password;
        $this->amount = $amount;
    }

    public function getAmount(): float {
        return $this->amount;
    }
}

$usersRepo = new AvocadoRepository(TestUser::class);

class AvocadoRepositoryTest extends TestCase {
    public function testCreatingNewModelInstancesByEntity() {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);

        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertTrue((($usersRepo -> findMany())[0]) instanceof TestUser);
    }

    public function testFindManyActionWithoutCriteria(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);
        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertIsArray($usersRepo -> findMany());
    }

    public function testFindManyActionWithCriteria(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);
        $usersRepo = new AvocadoRepository(TestUser::class);



        self::assertIsArray($usersRepo -> findMany(array(
            "username" => "test1"
        )));
    }

    public function testFindFindOne(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);

        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertIsObject($usersRepo -> findOne(array(
            "username" => "test1"
        )));
    }

    public function testFindOneWithCriteria(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);

        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertIsObject($usersRepo -> findOne(array(
            "username" => "%test%"
        )));
    }

    public function testUpdateMany() {

        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);

        $usersRepo = new AvocadoRepository(TestUser::class);
        $usersRepo -> updateMany(array("amount" => 10.0));

        $updatedUsers = $usersRepo -> findMany();

        $excepted = 10.0;

        self::assertSame($excepted, $updatedUsers[0]->getAmount());
        $usersRepo -> updateMany(array("amount" => 2.0));
    }

    public function testDelete(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);

        $usersRepo = new AvocadoRepository(TestUser::class);

        $usersRepo -> deleteMany(array(
            "username" => "test1"
        ));

        self::assertNull($usersRepo -> findOne(array(
            "username" => "test1"
        )));

        $usersRepo -> save(
            new TestUser("test1", "test1", 2.0)
        );
    }
}
