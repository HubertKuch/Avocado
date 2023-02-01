<?php

namespace Avocado\DataSource\Builder;

use Avocado\MysqlDriver\MySQLQueryBuilder;
use PHPUnit\Framework\TestCase;
use Avocado\Tests\Unit\UserRole;
use Avocado\Tests\Unit\TableWithIgnoringType;

class MySQLQueryBuilderTest extends TestCase {

    public function testFind() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString("SELECT * FROM test", $builder->find("test", [])->get());
    }

    public function testFindWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString("SELECT * FROM test WHERE  a = 2 AND  b LIKE \"asd\" AND c = null",
            $builder->find("test", ["a" => 2, "b" => "asd", "c" => null])->get()
        );
    }

    public function testUpdate() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString('UPDATE test SET  a = 24,  b = "null" ', $builder->update(
            "test",
            [
                "a" => 24,
                "b" => "null"
            ]
        )->get());
    }

    public function testUpdateWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString('UPDATE test SET  a = 2,  b = "asd"  test = 12 AND  test2 = null',
            $builder->update("test",
                ["a" => 2, "b" => "asd"],
                ["test" => 12, "test2" => null]
            )->get());
    }

    public function testDelete() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString('DELETE FROM test ', $builder->delete("test", [])->get());
    }

    public function testDeleteWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString('DELETE FROM test  WHERE  a = 2 AND  b LIKE "asd"', $builder->delete("test", ["a" => 2, "b" => "asd"])->get());
    }

    public function testSave() {
        $obj = new TableWithIgnoringType(null, UserRole::USER);

        $sql = MySQLQueryBuilder::save("test", $obj)->get();

        self::assertStringContainsString('INSERT INTO test (id, role) VALUE (NULL , "user")', $sql);
    }
}
