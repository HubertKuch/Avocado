<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Field;
use Avocado\ORM\Id;
use Avocado\ORM\Table;
use PHPUnit\Framework\TestCase;
use Avocado\ORM\AvocadoORMModel;

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

class AvocadoORMModelTest extends TestCase {
    public function testModelPrimaryKeyWithPassedItAsString(): void {
        $model = new AvocadoORMModel(TestModelWithIdAsString::class);

        $reflectionToModel = new \ReflectionClass($model);
        $primaryKey = $reflectionToModel->getProperty('primaryKey')->getValue($model);

        self::assertSame('id', $primaryKey);
    }

    public function testModelPrimaryKeyWithoutPassingIt(): void {
        $model = new AvocadoORMModel(TestModelWithoutPassedId::class);

        $reflectionToModel = new \ReflectionClass($model);
        $primaryKey = $reflectionToModel->getProperty('primaryKey')->getValue($model);

        self::assertSame('id', $primaryKey);
    }

}
