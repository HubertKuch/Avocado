<?php

class JsonPlaceholderTodo {
    public function __construct(
        private int $id,
        private int $userId,
        private string $title,
        private bool $completed
    ) {}

    public function getUserId(): int {
        return $this->userId;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function isCompleted(): bool {
        return $this->completed;
    }
}