<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

use RestaurantMockup\Models\Company;
use RestaurantMockup\Models\Employee;
use RestaurantMockup\Models\RestaurantChain;
use RestaurantMockup\Models\RestaurantLocation;
use RestaurantMockup\Models\User;

/** Explicit, configurable fixtures for the HTML form and POST exporter. */
final class RandomGenerator
{
    /**
     * Example-compatible: RandomGenerator::users($count, $count).
     * Arguments mean minimum and maximum generated user count (inclusive).
     * @return list<User>
     */
    public static function users(int $minimum, int $maximum): array
    {
        if ($minimum < 1 || $maximum > 100 || $minimum > $maximum) {
            throw new \InvalidArgumentException('User count must be between 1 and 100.');
        }
        $count = random_int($minimum, $maximum);
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $users[] = new User($i, RandomData::fullName(), "user{$i}@example.com");
        }
        return $users;
    }

    /**
     * Creates exactly $chainCount chains, each with exactly $locationCount stores
     * and $employeeCount staff members distributed round-robin across stores.
     * All salaries and ZIP codes fall within the inclusive ranges supplied.
     * @return list<RestaurantChain>
     */
    public static function chains(
        int $chainCount,
        int $employeeCount,
        int $locationCount,
        int $salaryMin,
        int $salaryMax,
        int $postalMin,
        int $postalMax,
    ): array {
        if ($chainCount < 1 || $chainCount > 10 || $employeeCount < 1 || $employeeCount > 100
            || $locationCount < 1 || $locationCount > 20 || $salaryMin < 0
            || $salaryMax > 1000000 || $salaryMin > $salaryMax || $postalMin < 0
            || $postalMax > 99999 || $postalMin > $postalMax) {
            throw new \InvalidArgumentException('Invalid chain generation configuration.');
        }

        $chains = [];
        $nextEmployeeId = 1;
        for ($i = 0; $i < $chainCount; $i++) {
            $company = Company::RandomGenerator();
            $chain = new RestaurantChain(
                $company->getName(), $company->getYearFounded(), $company->getDescription(),
                $company->getWebsite(), $company->getPhone(), $company->getIndustry(),
                $company->getCeo(), $company->isPubliclyTraded(), 1000 + $i,
                RandomData::cuisine(), random_int(0, 1) === 1, 'Independent',
            );
            $locations = [];
            for ($j = 0; $j < $locationCount; $j++) {
                [$city, $state] = RandomData::location();
                $locations[] = new RestaurantLocation(
                    RandomData::restaurantName(), RandomData::streetAddress(), $city, $state,
                    str_pad((string) random_int($postalMin, $postalMax), 5, '0', STR_PAD_LEFT),
                    random_int(0, 9) !== 0,
                );
            }
            for ($j = 0; $j < $employeeCount; $j++) {
                $title = RandomData::jobTitle();
                $id = $nextEmployeeId++;
                $employee = new Employee(
                    $id, RandomData::fullName(), "employee{$id}@example.com",
                    $title, (float) random_int($salaryMin, $salaryMax),
                    RandomData::startDate(), [$title],
                );
                $locations[$j % $locationCount]->addEmployee($employee);
            }
            foreach ($locations as $location) {
                $chain->addLocation($location);
            }
            $chains[] = $chain;
        }
        return $chains;
    }
}
