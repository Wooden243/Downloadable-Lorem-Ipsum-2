<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

/** No coercion of missing, array, decimal, exponent or malformed POST values. */
final class RequestValidator
{
    private const FORMATS = ['html', 'json', 'txt', 'md'];

    /** @param array<string, mixed> $post
     * @return array<string, int|string>
     */
    public static function validate(array $post): array
    {
        $format = self::choice($post, 'format', self::FORMATS);
        $dataset = array_key_exists('dataset', $post)
            ? self::choice($post, 'dataset', ['users', 'chains']) : 'users';
        if ($dataset === 'users') {
            return [
                'dataset' => 'users',
                'format' => $format,
                'count' => self::integer($post, 'count', 1, 100),
            ];
        }
        $params = [
            'dataset' => 'chains',
            'format' => $format,
            'chain_count' => self::integer($post, 'chain_count', 1, 10),
            'employee_count' => self::integer($post, 'employee_count', 1, 100),
            'location_count' => self::integer($post, 'location_count', 1, 20),
            'salary_min' => self::integer($post, 'salary_min', 0, 1000000),
            'salary_max' => self::integer($post, 'salary_max', 0, 1000000),
            'postal_min' => self::integer($post, 'postal_min', 0, 99999),
            'postal_max' => self::integer($post, 'postal_max', 0, 99999),
        ];
        if ($params['salary_min'] > $params['salary_max']) {
            throw new \InvalidArgumentException('salary_min cannot exceed salary_max.');
        }
        if ($params['postal_min'] > $params['postal_max']) {
            throw new \InvalidArgumentException('postal_min cannot exceed postal_max.');
        }
        return $params;
    }

    /** @param array<string, mixed> $post */
    private static function integer(array $post, string $key, int $min, int $max): int
    {
        $raw = $post[$key] ?? null;
        if (!is_string($raw) && !is_int($raw)) {
            throw new \InvalidArgumentException("Missing or invalid {$key}.");
        }
        $value = (string) $raw;
        if (!preg_match('/\A(?:0|[1-9][0-9]*)\z/D', $value) || strlen($value) > 10) {
            throw new \InvalidArgumentException("{$key} must be a whole number.");
        }
        $number = (int) $value;
        if ($number < $min || $number > $max) {
            throw new \InvalidArgumentException("{$key} must be between {$min} and {$max}.");
        }
        return $number;
    }

    /** @param array<string, mixed> $post
     * @param list<string> $allowed
     */
    private static function choice(array $post, string $key, array $allowed): string
    {
        $value = $post[$key] ?? null;
        if (!is_string($value) || !in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid or missing {$key}; choose " . implode(', ', $allowed) . '.');
        }
        return $value;
    }
}
