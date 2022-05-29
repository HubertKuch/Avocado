<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\IgnoreFieldType;
use Avocado\ORM\Attributes\Table;
use Avocado\ORM\AvocadoModelException;
use Avocado\ORM\AvocadoORMSettings;
use Avocado\ORM\AvocadoRepository;
use PHPUnit\Framework\TestCase;
use stdClass;

const DATABASE = "avocado_test_db";
const DSN = "mysql:host=127.0.0.1;dbname=".DATABASE;
const USER = "root";
const PASSWORD = "";

#[Table('users')]
class TestUser {
    #[Id('id')]
    private int $id;
    #[Field]
    private string $username;
    #[Field]
    private string $password;
    #[Field('amount2')]
    private float $amount;
    #[Field]
    private UserRole $role;

    public function __construct(string $username, string $password, float $amount, UserRole $role = UserRole::USER) {
        $this->username = $username;
        $this->password = $password;
        $this->amount = $amount;
        $this->role = $role;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getRole(): UserRole {
        return $this->role;
    }
}

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

    public function testMappingEnumsToSQL(): void {
        $testObject = new TableWithIgnoringType(1, UserRole::USER);
        $repository = new AvocadoRepository(TableWithIgnoringType::class);

        $ref = new \ReflectionObject($repository);
        self::assertSame(" \"user\" ", $ref->getMethod('getObjectAttributesAsSQLString')?->invokeArgs($repository, [$testObject]));
    }

    public function testMappingSQLToObjectIfEnumWasNotFound(): void {
        self::expectException(AvocadoModelException::class);

        $testObject = new stdClass();
        $testObject->id = 5;
        $testObject->username = "test";
        $testObject->password = "test";
        $testObject->role = "moderator";

        $repository = new AvocadoRepository(TableWithIgnoringType::class);

        $ref = new \ReflectionObject($repository);

        $ref->getMethod('sqlEntityToObject')?->invokeArgs($repository, [$testObject]);
    }

    public function testMappingSQLToObjectWithEnum(): void {
        $testObject = new stdClass();
        $testObject->id = 5;
        $testObject->username = "test";
        $testObject->password = "test";
        $testObject->role = "admin";

        $repository = new AvocadoRepository(TableWithIgnoringType::class);

        $ref = new \ReflectionObject($repository);

        $result = $ref->getMethod('sqlEntityToObject')?->invokeArgs($repository, [$testObject]);

        self::assertSame(UserRole::ADMIN, $result->getRole());
    }

    public function testSavingObjectsWithEnums(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);

        $testObject = new TestUser("test_with_enum", "test", 2.0, UserRole::USER);
        $repo = new AvocadoRepository(TestUser::class);

        $repo -> save($testObject);
        $testObject = $repo->findOne(["username" => "test_with_enum"]);

        self::assertSame(UserRole::USER, $testObject->getRole());
    }

    public function testFindingByEnumType(): void {
        AvocadoORMSettings::useDatabase(DSN, USER, PASSWORD);
        $repo = new AvocadoRepository(TestUser::class);

        $user = $repo -> findOne(["role" => UserRole::USER]);
        self::assertTrue($user instanceof TestUser && $user -> getRole() === UserRole::USER);
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
        $usersRepo -> updateMany(array("amount2" => 10.0));

        $updatedUsers = $usersRepo -> findMany();

        $excepted = 10.0;

        self::assertSame($excepted, $updatedUsers[0]->getAmount());
        $usersRepo -> updateMany(array("amount2" => 2.0));
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
            new TestUser("test1", "test1", 2.0, UserRole::USER)
        );
    }
}
