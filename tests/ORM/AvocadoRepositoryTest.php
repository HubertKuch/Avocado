<?php

namespace Avocado\Tests\Unit;

use stdClass;
use ReflectionObject;
use Avocado\ORM\Attributes\Id;
use PHPUnit\Framework\TestCase;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Table;
use Avocado\DataSource\DataSource;
use Avocado\ORM\AvocadoRepository;
use Avocado\ORM\AvocadoModelException;
use Avocado\DataSource\DataSourceBuilder;
use Avocado\AvocadoORM\Mappers\MySQLMapper;
use Avocado\DataSource\Database\DatabaseType;

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
        $usersRepo = $this->createMock(AvocadoRepository::class);
        $usersRepo->method("findMany")
            ->willReturn([new TestUser("", "", 24, UserRole::USER)]);

        self::assertTrue((($usersRepo -> findMany())[0]) instanceof TestUser);
    }

    public function testFindManyActionWithoutCriteria(): void {
        $usersRepo = $this->createMock(AvocadoRepository::class);
        $usersRepo->method("findMany")
            ->willReturn([new TestUser("", "", 24, UserRole::USER)]);

        self::assertIsArray($usersRepo -> findMany());
    }

    public function testFindManyActionWithCriteria(): void {
        $usersRepo = $this->createMock(AvocadoRepository::class);
        $usersRepo->method("findMany")
            ->willReturn([new TestUser("test1", "", 24, UserRole::USER)]);

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

        $mapper = new MySQLMapper();
        $result = $mapper -> entityToObject($repository, $testObject);

        self::assertSame(UserRole::ADMIN, $result->getRole());
    }

    public function testSavingObjectsWithEnums(): void {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method('findFirst')
            ->willReturn(new TestUser("test_with_enum", "test", 2.0, UserRole::USER));

        $testObject = $repo->findFirst(["username" => "test_with_enum"]);

        self::assertSame(UserRole::USER, $testObject->getRole());
    }

    public function testFindingByEnumType(): void {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method("findFirst")
            ->willReturn(new TestUser("", "", 2, UserRole::USER));

        $user = $repo -> findFirst(["role" => UserRole::USER]);
        self::assertTrue($user instanceof TestUser && $user -> getRole() === UserRole::USER);
    }

    public function testFindFindOne(): void {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method("findFirst")
            ->willReturn(new TestUser("test1", "", 23, UserRole::USER));

        self::assertIsObject($repo -> findFirst(array(
            "username" => "test1"
        )));
    }

    public function testFindOneWithCriteria(): void {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method("findFirst")
            ->willReturn(new TestUser("test1", "", 23, UserRole::USER));

        self::assertIsObject($repo -> findFirst(array(
            "username" => "%test%"
        )));
    }

    public function testUpdateMany() {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method("findFirst")
            ->willReturn(new TestUser("", "", 10.0, UserRole::USER));

        $updatedUsers = $repo->findFirst();

        $excepted = 10.0;

        self::assertSame($excepted, $updatedUsers->getAmount());
    }

    public function testDelete(): void {
        $usersRepo = $this->createMock(AvocadoRepository::class);

        $usersRepo->method('findFirst')
            ->willReturn(null);

        self::assertNull($usersRepo -> findFirst(array(
            "username" => "test1"
        )));
    }
}
