<?php

namespace HTTP\JSON;

class JSONFactoryTestUser {
    private int $id = 1;
    public string $username;
    private bool $isAdmin;

    public function __construct(string $username, bool $isAdmin) {
        $this->username = $username;
        $this->isAdmin = $isAdmin;
    }
}
