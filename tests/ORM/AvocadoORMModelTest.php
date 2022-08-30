<?php

namespace Avocado\Tests\Unit;

use ReflectionClass;
use ReflectionObject;
use Avocado\ORM\AvocadoModel;
use Avocado\ORM\Attributes\Id;
use PHPUnit\Framework\TestCase;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Table;

class AvocadoORMModelTest extends TestCase {
    public function testModelPrimaryKeyWithPassedItAsString(): void {
        $model = new AvocadoModel(TestModelWithIdAsString::class);

        $reflectionToModel = new ReflectionClass($model);
        $primaryKey = $reflectionToModel->getProperty('primaryKey')->getValue($model);

        self::assertSame('id', $primaryKey);
    }

    public function testModelPrimaryKeyWithoutPassingIt(): void {
        $model = new AvocadoModel(TestModelWithoutPassedId::class);

        $reflectionToModel = new ReflectionClass($model);
        $primaryKey = $reflectionToModel->getProperty('primaryKey')->getValue($model);

        self::assertSame('id', $primaryKey);
    }

    public function testTableName(): void {
        $model = new AvocadoModel(TestModelWithIdAsString::class);
        $ref = new ReflectionClass($model);
        $tableName = $ref->getProperty('tableName')->getValue($model);

        self::assertSame('table', $tableName);
    }

    public function testIsPropertyIsEnum(): void {
        $model = new AvocadoModel(TableWithIgnoringType::class);
        $ref = new ReflectionObject($model);

        self::assertTrue($ref->getMethod('isPropertyIsEnum')?->invokeArgs($model, ["role"]));
    }

    public function testIsPropertyIsNotEnum(): void {
        $model = new AvocadoModel(TableWithIgnoringType::class);
        $ref = new ReflectionObject($model);

        self::assertFalse($ref->getMethod('isPropertyIsEnum')?->invokeArgs($model, ["id"]));
    }
}
