<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Models\Employee;
use RestaurantMockup\Models\RestaurantChain;
use RestaurantMockup\Models\User;
use RestaurantMockup\Support\Exporter;
use RestaurantMockup\Support\RandomGenerator;
use RestaurantMockup\Support\RequestValidator;

$passed = 0;
$failed = 0;

function check(bool $condition, string $description): void
{
    global $passed, $failed;
    if (!$condition) {
        $failed++;
        echo "FAIL {$description}\n";
        return;
    }
    $passed++;
    echo "PASS {$description}\n";
}

function rejects(callable $action): bool
{
    try {
        $action();
    } catch (InvalidArgumentException $exception) {
        return true;
    }
    return false;
}

$users = RandomGenerator::users(5, 5);
check(count($users) === 5, 'users(): exact requested count');
check($users[0] instanceof User && $users[0] instanceof FileConvertible, 'User implements FileConvertible');
check(count(array_unique(array_map(static fn(User $u): int => $u->getId(), $users))) === 5, 'User IDs are unique');
check($users[0]->toArray()['id'] === 1, 'User toArray exposes ID');
check(str_contains($users[0]->toString(), 'Email:'), 'User toString serializes email');
check(str_contains($users[0]->toHTML(), 'user-line'), 'User toHTML renders a paragraph');
check(str_contains($users[0]->toMarkdown(), 'User #'), 'User toMarkdown renders a record');
check(rejects(static fn() => RandomGenerator::users(0, 5)), 'Generator rejects min user count 0');
check(rejects(static fn() => RandomGenerator::users(10, 101)), 'Generator rejects max user count >100');
check(rejects(static fn() => RandomGenerator::users(5, 4)), 'Generator rejects inverted user count');

$settings = [
    'dataset' => 'chains', 'format' => 'json', 'chain_count' => '2',
    'employee_count' => '7', 'location_count' => '3',
    'salary_min' => '42000', 'salary_max' => '43000',
    'postal_min' => '20', 'postal_max' => '50',
];
$params = RequestValidator::validate($settings);
check($params['chain_count'] === 2 && $params['employee_count'] === 7, 'Chain count fields validated');
check($params['salary_min'] === 42000 && $params['postal_max'] === 50, 'Range fields validated');
$chains = RandomGenerator::chains(2, 7, 3, 42000, 43000, 20, 50);
check(count($chains) === 2, 'Generator returns exactly 2 chains');
check($chains[0] instanceof RestaurantChain && $chains[0] instanceof FileConvertible, 'RestaurantChain preserves inheritance');
check(count($chains[0]->getRestaurantLocations()) === 3, 'Each chain has exactly 3 locations');
$allIds = [];
$allEmployeesCorrect = true;
$allPostalCorrect = true;
$allSalariesCorrect = true;
foreach ($chains as $chain) {
    $employeeNumber = 0;
    foreach ($chain->getRestaurantLocations() as $location) {
        $postcode = (int) $location->getPostalCode();
        $allPostalCorrect = $allPostalCorrect && strlen($location->getPostalCode()) === 5 && $postcode >= 20 && $postcode <= 50;
        foreach ($location->getEmployees() as $employee) {
            $allSalariesCorrect = $allSalariesCorrect && $employee instanceof Employee
                && $employee->getSalary() >= 42000 && $employee->getSalary() <= 43000;
            $employeeNumber++;
            $allIds[] = $employee->getId();
        }
    }
    $allEmployeesCorrect = $allEmployeesCorrect && $employeeNumber === 7;
}
check($allEmployeesCorrect, 'Exactly 7 employees total per chain');
check($allPostalCorrect, 'Postal codes formatted with 5 digits inside inclusive range');
check($allSalariesCorrect, 'Salaries remain inside requested inclusive range');
check(count(array_unique($allIds)) === count($allIds), 'Employee IDs unique across chains');
check($chains[0]->toArray()['numberOfLocations'] === 3, 'Chain JSON has location count');
check(count($chains[0]->toArray()['restaurantLocations'][0]['employees']) >= 2, 'Chain JSON retains nested employee data');
check(str_contains($chains[0]->toHTML(), '<details'), 'Chain HTML has expandable location');
check(str_contains($chains[0]->toMarkdown(), 'Restaurant Chain'), 'Chain Markdown includes heading');

foreach (['users' => $users, 'chains' => $chains] as $dataset => $items) {
    foreach (['html', 'json', 'txt', 'md'] as $format) {
        $result = Exporter::render($items, $format, $dataset);
        check(str_contains($result['contentType'], 'charset=UTF-8'), "{$dataset}/{$format} content type UTF-8");
        check(strlen($result['body']) > 30, "{$dataset}/{$format} nonempty content");
        check(str_starts_with($result['disposition'], $format === 'html' ? 'inline' : 'attachment'), "{$dataset}/{$format} download disposition");
        if ($format === 'json') {
            $data = json_decode($result['body'], true, 512, JSON_THROW_ON_ERROR);
            check(count($data) === count($items), "{$dataset}/json valid JSON and count");
        }
    }
}

check(RequestValidator::validate(['count' => '5', 'format' => 'md'])['dataset'] === 'users', 'Dataset defaults to users for sample-compatible POST');
check(rejects(static fn() => RequestValidator::validate(['count' => '5'])), 'Missing format rejected');
check(rejects(static fn() => RequestValidator::validate(['format' => 'json'])), 'Missing count rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '0', 'format' => 'json'])), 'Count below 1 rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '101', 'format' => 'json'])), 'Count above 100 rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '2.5', 'format' => 'json'])), 'Decimal count rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '1e2', 'format' => 'json'])), 'Exponent count rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => ['5'], 'format' => 'json'])), 'Array injection count rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '5', 'format' => ['json']])), 'Array injection format rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '5', 'format' => 'xml'])), 'Unsupported output type rejected');
check(rejects(static fn() => RequestValidator::validate(['dataset' => 'wrong', 'format' => 'json', 'count' => '5'])), 'Unknown dataset rejected');
$badSalary = $settings;
$badSalary['salary_min'] = '100000';
check(rejects(static fn() => RequestValidator::validate($badSalary)), 'Inverted salary range rejected');
$badPostal = $settings;
$badPostal['postal_min'] = '99';
check(rejects(static fn() => RequestValidator::validate($badPostal)), 'Inverted postal code range rejected');
$badPostal['postal_min'] = '0';
$badPostal['postal_max'] = '100000';
check(rejects(static fn() => RequestValidator::validate($badPostal)), 'Postal code >99999 rejected');
$badSettings = $settings;
$badSettings['location_count'] = '21';
check(rejects(static fn() => RequestValidator::validate($badSettings)), 'Location count >20 rejected');
check(rejects(static fn() => RandomGenerator::chains(1, 3, 2, 0, 2000000, 0, 99999)), 'Direct generator rejects out-of-range salaries');
check(rejects(static fn() => Exporter::render($users, 'xml', 'users')), 'Exporter refuses unrecognized format');

// HTML escaping should prevent a model containing markup from injecting elements.
$dangerous = new User(100, '<script>alert(1)</script>', 'safe@example.com');
$html = Exporter::render([$dangerous], 'html', 'users')['body'];
check(!str_contains($html, '<script>'), 'HTML export escapes a script tag');
check(str_contains($html, '&lt;script&gt;'), 'HTML export shows escaped script as text');

// Ensure zero-padded codes are not converted to numbers in JSON.
$zeroZipChain = RandomGenerator::chains(1, 1, 1, 10, 10, 5, 5)[0];
$json = Exporter::render([$zeroZipChain], 'json', 'chains')['body'];
check(str_contains($json, '"postalCode": "00005"'), 'JSON preserves zero-padded ZIP string');
check($zeroZipChain->getRestaurantLocations()[0]->getEmployees()[0]->getSalary() === 10.0, 'Inclusive salary bounds can be equal');

printf("\nSUMMARY: %d passed, %d failed\n", $passed, $failed);
exit($failed > 0 ? 1 : 0);
