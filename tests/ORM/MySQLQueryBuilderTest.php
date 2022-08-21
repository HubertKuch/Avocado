<?php

namespace Avocado\DataSource\Builder;

use PHPUnit\Framework\TestCase;

class MySQLQueryBuilderTest extends TestCase {

    public function testFind() {
        $builder = new MySQLQueryBuilder();

        self::assertSame("SELECT * FROM test", $builder->find("test", []));
    }

    public function testFindWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertSame("SELECT * FROM test WHERE  a = 2 AND  b LIKE \"asd\" AND c = null", $builder->find("test", ["a" => 2, "b" => "asd", "c" => null]));
    }

    public function testUpdate() {
        $builder = new MySQLQueryBuilder();

        self::assertSame('UPDATE test SET  a = 24,  b = "null" ', $builder->update(
            "test",
            [
                "a" => 24,
                "b" => "null"
            ]
        ));
    }

    public function testUpdateWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertSame('UPDATE test SET  a = 2,  b = "asd"  test = 12 AND  test2 = null',
            $builder->update("test",
                ["a" => 2, "b" => "asd"],
                ["test" => 12, "test2" => null]
            ));
    }

    public function testDelete() {
        $builder = new MySQLQueryBuilder();

        self::assertSame('DELETE FROM test ', $builder->delete("test", []));
    }

    public function testDeleteWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertSame('DELETE FROM test  a = 2 AND  b LIKE "asd"', $builder->delete("test", ["a" => 2, "b" => "asd"]));
    }
}
