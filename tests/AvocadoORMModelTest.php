<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;
use Avocado\ORM\AvocadoModel;
use Avocado\ORM\AvocadoORMSettings;
use Avocado\ORM\AvocadoRepository;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use stdClass;

#[Table('table')]
class TestModelWithIdAsString {
    #[Id('id')]
    private int $id;
    #[Field]
    private string $field;
}

#[Table('table2')]
class TestModelWithoutPassedId {
    #[Id]
    private int $id;
    #[Field]
    private string $field;
}

enum UserRole: string {
    case ADMIN = 'admin';
    case USER = 'user';
}

#[Table('users')]
class TableWithIgnoringType{
    #[Id]
    private int $id;
    #[Field]
    private UserRole $role;

    public function __construct(int $id, UserRole $role) {
        $this->id = $id;
        $this->role = $role;
    }

    public function getRole(): UserRole {
        return $this->role;
    }
}

class AvocadoORMModelTest extends TestCase {
    public function testModelPrimaryKeyWithPassedItAsString(): void {
        $model = new AvocadoModel(TestModelWithIdAsString::class);

        $reflectionToModel = new \ReflectionClass($model);
        $primaryKey = $reflectionToModel->getProperty('primaryKey')->getValue($model);

        self::assertSame('id', $primaryKey);
    }

    public function testModelPrimaryKeyWithoutPassingIt(): void {
        $model = new AvocadoModel(TestModelWithoutPassedId::class);

        $reflectionToModel = new \ReflectionClass($model);
        $primaryKey = $reflectionToModel->getProperty('primaryKey')->getValue($model);

        self::assertSame('id', $primaryKey);
    }

    public function testTableName(): void {
        $model = new AvocadoModel(TestModelWithIdAsString::class);
        $ref = new \ReflectionClass($model);
        $tableName = $ref->getProperty('tableName')->getValue($model);

        self::assertSame('table', $tableName);
    }

    public function testIsPropertyIsEnum(): void {
        $model = new AvocadoModel(TableWithIgnoringType::class);
        $ref = new \ReflectionObject($model);

        self::assertTrue($ref->getMethod('isPropertyIsEnum')?->invokeArgs($model, ["role"]));
    }

    public function testIsPropertyIsNotEnum(): void {
        $model = new AvocadoModel(TableWithIgnoringType::class);
        $ref = new ReflectionObject($model);

        self::assertFalse($ref->getMethod('isPropertyIsEnum')?->invokeArgs($model, ["id"]));
    }
}
