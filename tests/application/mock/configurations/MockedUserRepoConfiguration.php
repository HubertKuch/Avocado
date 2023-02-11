<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\AvocadoApplication\Attributes\Configuration;
use Avocado\AvocadoApplication\Attributes\Leaf;
use Avocado\ORM\AvocadoRepository;
use Avocado\Tests\Unit\TestUser;

#[Configuration]
class MockedUserRepoConfiguration {

    #[Leaf("test_user_repository")]
    public function getTestUserRepository(): AvocadoRepository {
        return new AvocadoRepository(TestUser::class);
    }

}