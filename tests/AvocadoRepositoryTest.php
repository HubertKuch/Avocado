<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Field;
use Avocado\ORM\Id;
use Avocado\ORM\Table;
use phpDocumentor\Reflection\Types\Void_;
use PhpParser\Builder\Use_;
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
    #[Field]
    private float $amount;

    public function __construct(string $username, string $password, float $amount) {
        $this->username = $username;
        $this->password = $password;
        $this->amount = $amount;
    }
}

class AvocadoRepositoryTest extends TestCase {
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

        self::assertIsArray($usersRepo -> findOne(array(
            "username" => "test1"
        )));
    }

    public function testFindOneWithCriteria(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);

        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertIsArray($usersRepo -> findOne(array(
            "username" => "%test%"
        )));
    }

    public function testUpdateMany(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);

        $usersRepo = new AvocadoRepository(TestUser::class);
        $usersRepo -> updateMany(array("amount" => 10.0));

        $updatedUsers = $usersRepo -> findMany();

        $excepted = 10.0;

        self::assertSame($excepted, $updatedUsers[0]['amount']);
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