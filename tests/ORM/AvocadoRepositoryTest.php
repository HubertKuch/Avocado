<?php

namespace Avocado\Tests\Unit;

use Avocado\DataSource\DataSource;
use Avocado\DataSource\DataSourceBuilder;
use Avocado\MysqlDriver\MySQLDriver;
use Avocado\MysqlDriver\MySQLMapper;
use Avocado\ORM\AvocadoModelException;
use Avocado\ORM\AvocadoORMSettings;
use Avocado\ORM\AvocadoRepository;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use stdClass;

class AvocadoRepositoryTest extends TestCase {
    private DataSource $dataSource;

    private const DATABASE = "avocado_test";
    private const USER = "user";
    private const PASSWORD = "user";

    protected function setUp(): void {
        $this->dataSource = (new DataSourceBuilder())->username(self::USER)
                                                     ->password(self::PASSWORD)
                                                     ->databaseName(self::DATABASE)
                                                     ->driver(MySQLDriver::class)
                                                     ->port(3306)
                                                     ->server("172.17.0.2")
                                                     ->build();

        AvocadoORMSettings::fromExistingSource($this->dataSource);
    }

    public function testCreatingNewModelInstancesByEntity() {
        $usersRepo = $this->createMock(AvocadoRepository::class);
        $usersRepo->method("findMany")->willReturn([new TestUser("", "", 24, UserRole::USER)]);

        self::assertTrue((($usersRepo->findMany())[0]) instanceof TestUser);
    }

    public function testFindManyActionWithoutCriteria(): void {
        $usersRepo = $this->createMock(AvocadoRepository::class);
        $usersRepo->method("findMany")->willReturn([new TestUser("", "", 24, UserRole::USER)]);

        self::assertIsArray($usersRepo->findMany());
    }

    public function testFindManyActionWithCriteria(): void {
        $usersRepo = $this->createMock(AvocadoRepository::class);
        $usersRepo->method("findMany")->willReturn([new TestUser("test1", "", 24, UserRole::USER)]);

        self::assertIsArray($usersRepo->findMany(array("username" => "test1")));
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
        $result = $mapper->entityToObject($repository, $testObject);

        self::assertSame(UserRole::ADMIN, $result->getRole());
    }

    public function testSavingObjectsWithEnums(): void {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method('findFirst')->willReturn(new TestUser("test_with_enum", "test", 2.0, UserRole::USER));

        $testObject = $repo->findFirst(["username" => "test_with_enum"]);

        self::assertSame(UserRole::USER, $testObject->getRole());
    }

    public function testFindingByEnumType(): void {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method("findFirst")->willReturn(new TestUser("", "", 2, UserRole::USER));

        $user = $repo->findFirst(["role" => UserRole::USER]);
        self::assertTrue($user instanceof TestUser && $user->getRole() === UserRole::USER);
    }

    public function testFindFindOne(): void {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method("findFirst")->willReturn(new TestUser("test1", "", 23, UserRole::USER));

        self::assertIsObject($repo->findFirst(array("username" => "test1")));
    }

    public function testFindOneWithCriteria(): void {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method("findFirst")->willReturn(new TestUser("test1", "", 23, UserRole::USER));

        self::assertIsObject($repo->findFirst(array("username" => "%test%")));
    }

    public function testUpdateMany() {
        $repo = $this->createMock(AvocadoRepository::class);

        $repo->method("findFirst")->willReturn(new TestUser("", "", 10.0, UserRole::USER));

        $updatedUsers = $repo->findFirst();

        $excepted = 10.0;

        self::assertSame($excepted, $updatedUsers->getAmount());
    }

    public function testDelete(): void {
        $usersRepo = $this->createMock(AvocadoRepository::class);

        $usersRepo->method('findFirst')->willReturn(null);

        self::assertNull($usersRepo->findFirst(array("username" => "test1")));
    }

    public function testGivenModelWithOneToManyRelation_thenQuery_returnValidParsedJoinedObjects() {
        $repo = new AvocadoRepository(TestUserWithOneToMany::class);
        $users = $repo->findMany();

        self::assertTrue($users[0] instanceof TestUserWithOneToMany);
        self::assertTrue($users[0]->getBooks()[0] instanceof TestBook);
    }

    public function testGivenModelWithOneToOneRelation_thenQueryReturnValidParsedJoinedObjects() {
        $repo = new AvocadoRepository(TestBook::class);
        $books = $repo->findMany();

        self::assertTrue($books[0] instanceof TestBook);
        self::assertTrue($books[0]->getDetails() instanceof TestBookDetails);
    }

    public function testGivenModelWithManyToOneRelation_thenQuery_returnValidParsedJoinedObjects() {
        $repo = new AvocadoRepository(TestBookWithManyToOneRelation::class);
        $books = $repo->findMany();

        self::assertTrue($books[0] instanceof TestBookWithManyToOneRelation);
        self::assertTrue($books[0]->getDetails() instanceof TestBookDetails);
        self::assertTrue($books[0]->getUser() instanceof TestUser);
    }

    public function testGivenValidEntityWithOneToOneRelation_thenSave_endWithoutExceptions() {
        $bookRepo = new AvocadoRepository(TestBook::class);
        $bookDetailsRepo = new AvocadoRepository(TestBookDetails::class);

        $bookId = rand(1, 1000);

        try {
            $book = new TestBook($bookId,
                "No longer human",
                "",
                new TestBookDetails('2023-03-22 09:13:52', '2023-03-22 09:13:52'),
                1);

            $bookRepo->transactionSave($book);

            self::assertNotNull($bookRepo->findById($bookId));
            self::assertNotNull($bookDetailsRepo->findById($bookId));
        } finally {
            $bookDetailsRepo->deleteOneById($bookId);
            $bookRepo->deleteOneById($bookId);
        }
    }

    public function testGivenValidEntityWithOneToManyRelation_thenSave_endWithoutExceptions() {}

    public function testGivenValidEntityWithManyToOneRelation_thenSave_endWithoutExceptions() {}
}
