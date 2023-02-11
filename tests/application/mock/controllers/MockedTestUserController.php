<?php

namespace Avocado\Tests\Unit\Application;

use Avocado\Application\RestController;
use Avocado\ORM\AvocadoRepository;
use AvocadoApplication\Attributes\Autowired;
use AvocadoApplication\Attributes\BaseURL;
use AvocadoApplication\Mappings\GetMapping;

#[RestController]
#[BaseURL("/avocado-test")]
class MockedTestUserController {

    #[Autowired(autowiredResourceName: "test_user_repository")]
    private readonly AvocadoRepository $repository;

    #[GetMapping("/test-users/")]
    public function getUsers(): array {
        return $this->repository->findMany();
    }

}