<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\AvocadoModel;
use Avocado\ORM\Attributes\IgnoreFieldType;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Table;
use Avocado\ORM\Attributes\Id;
use PHPUnit\Framework\TestCase;

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
    case ADMIN = 'ADMIN';
    case USER = 'USER';
}

#[Table('table3')]
class TableWithIgnoringType{
    #[Id]
    private int $id;
    #[IgnoreFieldType]
    private UserRole $role;
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
}
