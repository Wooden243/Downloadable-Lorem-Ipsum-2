<?php
declare(strict_types=1);

namespace RestaurantMockup\Models;

use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Support\Html;
use RestaurantMockup\Support\RandomData;

class User implements FileConvertible
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
    ) {
        if ($id <= 0 || trim($name) === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('A user needs a positive ID, name and valid email.');
        }
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }

    public static function RandomGenerator(): self
    {
        $id = random_int(1000, 999999);
        return new self($id, RandomData::fullName(), "user{$id}@example.com");
    }

    /** @return array{id: int, name: string, email: string} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email];
    }

    public function toString(): string
    {
        return "User ID: {$this->id}; Name: {$this->name}; Email: {$this->email}";
    }

    public function toHTML(): string
    {
        return '<p class="user-line">' . Html::escape($this->toString()) . '</p>';
    }

    public function toMarkdown(): string
    {
        return '- User #' . $this->id . ': ' . Html::markdown($this->name)
            . ' (' . Html::markdown($this->email) . ')';
    }
}
