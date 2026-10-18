<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

use RestaurantMockup\Support\Exporter;
use RestaurantMockup\Support\RandomGenerator;
use RestaurantMockup\Support\RequestValidator;

// Download responses must not leak form values or HTML before HTTP headers.
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; frame-ancestors 'none'");

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Method Not Allowed. Submit the form using POST.\n";
    exit;
}

try {
    $parameters = RequestValidator::validate($_POST);
    if ($parameters['dataset'] === 'users') {
        $items = RandomGenerator::users($parameters['count'], $parameters['count']);
    } else {
        $items = RandomGenerator::chains(
            $parameters['chain_count'], $parameters['employee_count'],
            $parameters['location_count'], $parameters['salary_min'],
            $parameters['salary_max'], $parameters['postal_min'], $parameters['postal_max'],
        );
    }
    $export = Exporter::render($items, $parameters['format'], $parameters['dataset']);
    http_response_code(200);
    header('Content-Type: ' . $export['contentType']);
    header('Content-Disposition: ' . $export['disposition']);
    echo $export['body'];
} catch (\InvalidArgumentException $exception) {
    http_response_code(412);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Invalid request: ' . $exception->getMessage() . "\n";
} catch (\Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Internal server error.\n";
}
