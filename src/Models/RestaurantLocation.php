<?php
declare(strict_types=1);

namespace RestaurantMockup\Models;

use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Support\Html;
use RestaurantMockup\Support\RandomData;

class RestaurantLocation implements FileConvertible
{
    /** @var list<Employee> */
    private array $employees = [];

    public function __construct(
        private string $name,
        private string $address,
        private string $city,
        private string $state,
        private string $postalCode,
        private bool $isOpen,
    ) {
        if (trim($name) === '' || trim($address) === '' || trim($city) === '' || trim($state) === '' || trim($postalCode) === '') {
            throw new \InvalidArgumentException('Restaurant location fields may not be empty.');
        }
    }

    public function getName(): string { return $this->name; }
    public function getAddress(): string { return $this->address; }
    public function getCity(): string { return $this->city; }
    public function getState(): string { return $this->state; }
    public function getPostalCode(): string { return $this->postalCode; }
    public function isOpen(): bool { return $this->isOpen; }
    /** @return list<Employee> */
    public function getEmployees(): array { return $this->employees; }

    public function addEmployee(Employee $employee): void
    {
        foreach ($this->employees as $existing) {
            if ($existing->getId() === $employee->getId()) {
                throw new \InvalidArgumentException('Duplicate employee ID at this location.');
            }
        }
        $this->employees[] = $employee;
    }

    public static function RandomGenerator(): self
    {
        [$city, $state] = RandomData::location();
        $location = new self(
            RandomData::restaurantName(), RandomData::streetAddress(), $city, $state,
            RandomData::postalCode(), random_int(0, 9) !== 0,
        );
        for ($i = 0, $count = random_int(2, 5); $i < $count; $i++) {
            $employee = Employee::RandomGenerator();
            // Extremely rare random ID collision: retry instead of failing mock generation.
            $ids = array_map(static fn(Employee $existing): int => $existing->getId(), $location->employees);
            while (in_array($employee->getId(), $ids, true)) {
                $employee = Employee::RandomGenerator();
            }
            $location->addEmployee($employee);
        }
        return $location;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postalCode' => $this->postalCode,
            'isOpen' => $this->isOpen,
            'employees' => array_map(
                static fn(Employee $employee): array => $employee->toArray(),
                $this->employees,
            ),
        ];
    }

    public function toString(): string
    {
        $employeeLines = array_map(static fn(Employee $employee): string => '  ' . $employee->toString(), $this->employees);
        return sprintf(
            "Location: %s; Address: %s, %s, %s %s; Open: %s\nEmployees:\n%s",
            $this->name, $this->address, $this->city, $this->state, $this->postalCode,
            $this->isOpen ? 'Yes' : 'No', implode("\n", $employeeLines),
        );
    }

    public function toHTML(): string
    {
        $name = Html::escape($this->name);
        $address = Html::escape($this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $this->postalCode);
        $status = $this->isOpen ? 'Open' : 'Closed';
        $html = '<details class="location"><summary>' . $name . '</summary><div class="location-body">';
        $html .= '<p><strong>Address:</strong> ' . $address . '</p>';
        $html .= '<p><strong>Status:</strong> ' . $status . '</p>';
        $html .= '<h4>Employees (' . count($this->employees) . ')</h4><ul class="employee-list">';
        foreach ($this->employees as $employee) {
            $html .= $employee->toHTML();
        }
        return $html . '</ul></div></details>';
    }

    public function toMarkdown(): string
    {
        $out = '### ' . Html::markdown($this->name) . "\n\n";
        $out .= '- Address: ' . Html::markdown($this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $this->postalCode) . "\n";
        $out .= '- Open: ' . ($this->isOpen ? 'Yes' : 'No') . "\n";
        $out .= '- Employees (' . count($this->employees) . "): \n";
        foreach ($this->employees as $employee) {
            $out .= '  ' . $employee->toMarkdown() . "\n";
        }
        return $out . "\n";
    }
}
