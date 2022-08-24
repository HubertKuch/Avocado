<?php

namespace Avocado\Tests\Unit;

use Avocado\AvocadoORM\Mappers\MySQLMapper;
use Avocado\DataSource\Database\DatabaseType;
use Avocado\DataSource\DataSource;
use Avocado\DataSource\DataSourceBuilder;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;
use Avocado\ORM\AvocadoModelException;
use Avocado\ORM\AvocadoORMSettings;
use Avocado\ORM\AvocadoRepository;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use stdClass;

const DATABASE = "avocado_test_db";
const DSN = "mysql:host=127.0.0.1;dbname=".DATABASE;
const USER = "root";
const PASSWORD = "";

#[Table('users')]
class TestUser {
    #[Id('id')]
    private ?int $id = null;
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

    public function setRole(UserRole $role): void {
        $this->role = $role;
    }
}

class AvocadoRepositoryTest extends TestCase {
    private DataSource $dataSource;

    protected function setUp(): void {
        $this->dataSource = (new DataSourceBuilder())
            ->username(USER)
            ->password(PASSWORD)
            ->databaseName(DATABASE)
            ->databaseType(DatabaseType::MYSQL)
            ->port(3306)
            ->server("127.0.0.1")
            ->build();
    }

    public function testCreatingNewModelInstancesByEntity() {
        AvocadoORMSettings::fromExistingSource($this->dataSource);

        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertTrue((($usersRepo -> findMany())[0]) instanceof TestUser);
    }

    public function testFindManyActionWithoutCriteria(): void {
        AvocadoORMSettings::fromExistingSource($this->dataSource);

        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertIsArray($usersRepo -> findMany());
    }

    public function testFindManyActionWithCriteria(): void {
        AvocadoORMSettings::fromExistingSource($this->dataSource);

        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertIsArray($usersRepo -> findMany(array(
            "username" => "test1"
        )));
    }

    public function testMappingEnumsToSQL(): void {
        $testObject = new TableWithIgnoringType(1, UserRole::USER);
        $repository = new AvocadoRepository(TableWithIgnoringType::class);

        $ref = new ReflectionObject($repository);
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

        $ref = new ReflectionObject($repository);

        $mapper = new MySQLMapper();

        $mapper->entityToObject($repository, $testObject);
    }

    public function testMappingSQLToObjectWithEnum(): void {
        $testObject = new stdClass();
        $testObject->id = 5;
        $testObject->username = "test";
        $testObject->password = "test";
        $testObject->role = "admin";

        $repository = new AvocadoRepository(TableWithIgnoringType::class);

        $ref = new ReflectionObject($repository);

        $mapper = new MySQLMapper();
        $result = $mapper -> entityToObject($repository, $testObject);

        self::assertSame(UserRole::ADMIN, $result->getRole());
    }

    public function testSavingObjectsWithEnums(): void {
        AvocadoORMSettings::fromExistingSource($this->dataSource);


        $testObject = new TestUser("test_with_enum", "test", 2.0, UserRole::USER);
        $repo = new AvocadoRepository(TestUser::class);

        $repo -> save($testObject);
        $testObject = $repo->findFirst(["username" => "test_with_enum"]);

        self::assertSame(UserRole::USER, $testObject->getRole());
    }

    public function testFindingByEnumType(): void {
        AvocadoORMSettings::fromExistingSource($this->dataSource);

        $repo = new AvocadoRepository(TestUser::class);

        $user = $repo -> findFirst(["role" => UserRole::USER]);
        self::assertTrue($user instanceof TestUser && $user -> getRole() === UserRole::USER);
    }

    public function testFindFindOne(): void {
        AvocadoORMSettings::fromExistingSource($this->dataSource);


        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertIsObject($usersRepo -> findFirst(array(
            "username" => "test1"
        )));
    }

    public function testFindOneWithCriteria(): void {
        AvocadoORMSettings::fromExistingSource($this->dataSource);


        $usersRepo = new AvocadoRepository(TestUser::class);

        self::assertIsObject($usersRepo -> findFirst(array(
            "username" => "%test%"
        )));
    }

    public function testUpdateMany() {
        AvocadoORMSettings::fromExistingSource($this->dataSource);


        $usersRepo = new AvocadoRepository(TestUser::class);
        $usersRepo -> updateMany(array("amount2" => 10.0));

        $updatedUsers = $usersRepo -> findMany();

        $excepted = 10.0;

        self::assertSame($excepted, $updatedUsers[0]->getAmount());
        $usersRepo -> updateMany(array("amount2" => 2.0));
    }

    public function testDelete(): void {
        AvocadoORMSettings::fromExistingSource($this->dataSource);


        $usersRepo = new AvocadoRepository(TestUser::class);

        $usersRepo -> deleteMany(array(
            "username" => "test1"
        ));

        self::assertNull($usersRepo -> findFirst(array(
            "username" => "test1"
        )));

        $usersRepo -> save(
            new TestUser("test1", "test1", 2.0, UserRole::USER)
        );
    }
}
