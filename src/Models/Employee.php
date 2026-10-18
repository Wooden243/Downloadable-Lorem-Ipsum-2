<?php
declare(strict_types=1);

namespace RestaurantMockup\Models;

use DateTimeImmutable;
use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Support\Html;
use RestaurantMockup\Support\RandomData;

class Employee extends User implements FileConvertible
{
    /** @param list<string> $roles */
    public function __construct(
        int $id,
        string $name,
        string $email,
        private string $jobTitle,
        private float $salary,
        private DateTimeImmutable $startDate,
        private array $roles,
    ) {
        parent::__construct($id, $name, $email);
        if (trim($jobTitle) === '' || !is_finite($salary) || $salary < 0) {
            throw new \InvalidArgumentException('Job title and nonnegative finite salary are required.');
        }
        foreach ($roles as $role) {
            if (!is_string($role) || trim($role) === '') {
                throw new \InvalidArgumentException('Each employee role must be a nonempty string.');
            }
        }
    }

    public function getJobTitle(): string { return $this->jobTitle; }
    public function getSalary(): float { return $this->salary; }
    public function getStartDate(): DateTimeImmutable { return $this->startDate; }
    /** @return list<string> */
    public function getRoles(): array { return $this->roles; }

    public static function RandomGenerator(): self
    {
        $id = random_int(1000, 999999);
        $title = RandomData::jobTitle();
        return new self(
            $id, RandomData::fullName(), "employee{$id}@example.com",
            $title, (float) random_int(32000, 95000), RandomData::startDate(),
            [$title, 'Guest service'],
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'jobTitle' => $this->jobTitle,
            'salary' => $this->salary,
            'startDate' => $this->startDate->format('Y-m-d'),
            'roles' => $this->roles,
        ]);
    }

    public function toString(): string
    {
        return sprintf(
            'ID: %d; Job Title: %s; Name: %s; Salary: $%s; Start Date: %s; Roles: %s',
            $this->getId(), $this->jobTitle, $this->getName(),
            number_format($this->salary, 2), $this->startDate->format('Y-m-d'), implode(', ', $this->roles),
        );
    }

    public function toHTML(): string
    {
        return '<li class="employee-row">' . Html::escape($this->toString()) . '</li>';
    }

    public function toMarkdown(): string
    {
        return '- ' . Html::markdown($this->toString());
    }
}
