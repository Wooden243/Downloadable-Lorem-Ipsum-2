<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

use DateTimeImmutable;

/** Small self-contained fake-data vocabulary; no external Faker dependency. */
final class RandomData
{
    private const FIRST = ['Avery','Jordan','Morgan','Taylor','Riley','Casey','Alex','Jamie','Robin','Drew','Parker','Quinn','Noah','Harper','Emery'];
    private const LAST = ['Reed','Martin','Patel','Kim','Santos','Brooks','Murphy','Chen','Taylor','Rivera','Cooper','Bennett','Lewis','Carter','Hayes'];
    private const CITY_STATES = [
        ['Seattle','WA'], ['Austin','TX'], ['Denver','CO'], ['Portland','OR'],
        ['Chicago','IL'], ['Boston','MA'], ['Phoenix','AZ'], ['Raleigh','NC'],
        ['San Diego','CA'], ['Nashville','TN'], ['Atlanta','GA'], ['Madison','WI'],
    ];
    private const STREET = ['Maple','Oak','Pine','Cedar','Union','Sunset','Broadway','Market','Willow','Lake'];
    private const CUISINES = ['Italian','Japanese','Mexican','American','Mediterranean','Thai','Indian'];
    private const COMPANY_PARTS = ['Harbor','Silver','Maple','Bluebird','Summit','River','Evergreen','Golden','Clover','Northstar'];
    private const JOBS = ['Cashier','Chef','Server','Shift Manager','Host','Line Cook','General Manager'];
    private const INDUSTRIES = ['Food & Beverage','Hospitality','Restaurant Operations'];

    public static function pick(array $values): mixed
    {
        if ($values === []) {
            throw new \InvalidArgumentException('Cannot pick from an empty array.');
        }
        return $values[random_int(0, count($values) - 1)];
    }

    public static function fullName(): string
    {
        return self::pick(self::FIRST) . ' ' . self::pick(self::LAST);
    }

    public static function companyName(): string
    {
        return self::pick(self::COMPANY_PARTS) . ' ' . self::pick(['Hospitality Group', 'Foods Inc.', 'Dining Co.', 'Restaurants LLC']);
    }

    public static function restaurantName(): string
    {
        return self::pick(self::COMPANY_PARTS) . ' ' . self::pick(['Kitchen','Bistro','Grill','Table','Cafe','Corner']);
    }

    public static function location(): array
    {
        return self::pick(self::CITY_STATES);
    }

    public static function streetAddress(): string
    {
        return random_int(10, 9800) . ' ' . self::pick(self::STREET) . ' ' . self::pick(['St','Ave','Blvd','Road']);
    }

    public static function postalCode(): string
    {
        return str_pad((string) random_int(1000, 99999), 5, '0', STR_PAD_LEFT);
    }

    public static function phone(): string
    {
        return sprintf('(%03d) %03d-%04d', random_int(201, 989), random_int(200, 999), random_int(0, 9999));
    }

    public static function cuisine(): string
    {
        return self::pick(self::CUISINES);
    }

    public static function jobTitle(): string
    {
        return self::pick(self::JOBS);
    }

    public static function industry(): string
    {
        return self::pick(self::INDUSTRIES);
    }

    public static function startDate(): DateTimeImmutable
    {
        $yearsAgo = random_int(0, 10);
        $daysAgo = random_int(0, 364);
        return (new DateTimeImmutable('today'))->modify("-{$yearsAgo} years")->modify("-{$daysAgo} days");
    }

    public static function yearFounded(): int
    {
        return random_int(1980, (int) date('Y'));
    }

    public static function safeDomain(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)) ?? 'restaurant');
        return 'https://' . trim($slug, '-') . '.example.com';
    }
}
