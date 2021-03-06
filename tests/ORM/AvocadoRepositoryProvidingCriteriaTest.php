<?php

namespace Avocado\Tests\Unit;

use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;
use Avocado\ORM\AvocadoRepository;
use Avocado\ORM\AvocadoRepositoryException;
use PHPUnit\Framework\TestCase;

#[Table('users')]
class TestUserModel {
    #[Id]
    private int $id;
    #[Field]
    private string $username;
    #[Field]
    private string $password;
    #[Field]
    private int $age;
    #[Field]
    private float $ammount;
    #[Field]
    private float $debt;
}

function provideCriteriaToTestQuery(string &$query, array $criteria): void {
    $usersRepository = new AvocadoRepository(TestUserModel::class);
    $reflectionToCriteriaMethod = new \ReflectionMethod(AvocadoRepository::class, 'provideCriteria');
    $reflectionToCriteriaMethod -> invokeArgs($usersRepository, array(&$query, $criteria));

    $excepted = 'SELECT * FROM users WHERE username LIKE "john"';
    $query = preg_replace('/\s\s+/', ' ', $query);
    $query = trim($query);
}

class AvocadoRepositoryCriteriaTest extends TestCase {
    public function testProvidingSingleCriteriaToQuery() {
        $testSQL = "SELECT * FROM users";
        $criteria = array(
            "username" => "john"
        );

        $excepted = 'SELECT * FROM users WHERE username LIKE "john"';
        provideCriteriaToTestQuery($testSQL, $criteria);

        self::assertStringContainsString($testSQL, $excepted);
    }

    public function testProvidingSingleIntergerValueToQuery() {
        $sql = "SELECT * FROM users";
        $criteria = array(
            "id" => 1
        );

        $excepted = "SELECT * FROM users WHERE id = 1";
        provideCriteriaToTestQuery($sql, $criteria);

        self::assertStringContainsString($sql, $excepted);
    }

    public function testProvidingSingleDoubleValueToQuery() {
        $sql = "SELECT * FROM users";
        $criteria = array(
            "ammount" => 1.22
        );

        $excepted = "SELECT * FROM users WHERE ammount = 1.22";
        provideCriteriaToTestQuery($sql, $criteria);

        self::assertStringContainsString($sql, $excepted);
    }

    public function testProvidingMultipleDoubleValueToQuery() {
        $sql = "SELECT * FROM users";
        $criteria = array(
            "ammount" => 1.22,
            "debt" => 1.
        );

        $excepted = "SELECT * FROM users WHERE ammount = 1.22 AND debt = 1.0";
        provideCriteriaToTestQuery($sql, $criteria);

        self::assertStringContainsString($sql, $excepted);
    }

    public function testProvidingMultipleStringValuesToQuery() {
        $sql = "SELECT * FROM users";
        $criteria = array(
            "username" => "john",
            "password" => "doe"
        );

        $excepted = 'SELECT * FROM users WHERE username LIKE "john" AND password LIKE "doe"';
        provideCriteriaToTestQuery($sql, $criteria);

        self::assertStringContainsString($sql, $excepted);
    }

    public function testProvidingMultipleIntegerValuesToQuery() {
        $sql = "SELECT * FROM users";
        $criteria = array(
            "id" => 1,
            "age" => 18
        );

        $excepted = 'SELECT * FROM users WHERE id = 1 AND age = 18';
        provideCriteriaToTestQuery($sql, $criteria);

        self::assertStringContainsString($sql, $excepted);
    }

    public function testShouldThrowAvocadoRepositoryExceptionIfCriteriaHasDifferentKeysThanModelFields() {
        self::expectException(AvocadoRepositoryException::class);

        $userRepository = new AvocadoRepository(TestUserModel::class);
        $reflectionToProvideCriteriaMethod = new \ReflectionMethod($userRepository::class, 'provideCriteria');

        $sql = "";

        $criteria = array(
            "friend" => "s"
        );

        $reflectionToProvideCriteriaMethod -> invokeArgs($userRepository, array(&$sql, $criteria));
    }

    public function testShouldThrowAvocadoRepositoryExceptionIfCriteriaTypeIsDifferentThanModelFieldType() {
        self::expectException(AvocadoRepositoryException::class);

        $userRepository = new AvocadoRepository(TestUserModel::class);
        $reflectionToProvideCriteriaMethod = new \ReflectionMethod($userRepository::class, 'provideCriteria');

        $sql = "";
        $criteria = array(
            "username" => 1
        );

        $reflectionToProvideCriteriaMethod -> invokeArgs($userRepository, array(&$sql, $criteria));
    }

    public function testProvidingMixedTypeValuesToQuery() {
        $sql = "SELECT * FROM users";
        $criteria = array(
            "username" => "john",
            "age" => 18,
            "debt" => 10.2
        );

        $excepted = 'SELECT * FROM users WHERE username LIKE "john" AND age = 18 AND debt = 10.2';
        provideCriteriaToTestQuery($sql, $criteria);

        self::assertStringContainsString($sql, $excepted);
    }

}
