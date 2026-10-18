<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

use RestaurantMockup\Models\RestaurantChain;
use RestaurantMockup\Models\User;
use RestaurantMockup\Contracts\FileConvertible;

final class Exporter
{
    /** @param list<FileConvertible> $items
     * @return array{body: string, contentType: string, disposition: string}
     */
    public static function render(array $items, string $format, string $dataset): array
    {
        if (!in_array($format, ['html', 'json', 'txt', 'md'], true)
            || !in_array($dataset, ['users', 'chains'], true)) {
            throw new \InvalidArgumentException('Unsupported format or dataset.');
        }
        $title = $dataset === 'users' ? 'Generated Users' : 'Restaurant Chain Mockup';
        if ($format === 'json') {
            $arrays = array_map(
                static function (FileConvertible $item): array {
                    if (!$item instanceof User && !$item instanceof RestaurantChain) {
                        throw new \InvalidArgumentException('Cannot serialize unknown fixture type.');
                    }
                    return $item->toArray();
                },
                $items,
            );
            $body = json_encode($arrays, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
            $mime = 'application/json';
        } elseif ($format === 'txt') {
            $body = implode("\n\n", array_map(
                static fn(FileConvertible $item): string => $item->toString(), $items,
            )) . "\n";
            $mime = 'text/plain';
        } elseif ($format === 'md') {
            $body = '# ' . $title . "\n\n" . implode("\n\n", array_map(
                static fn(FileConvertible $item): string => $item->toMarkdown(), $items,
            )) . "\n";
            $mime = 'text/markdown';
        } else {
            $body = self::htmlDocument($items, $title);
            $mime = 'text/html';
        }
        $filename = $dataset === 'users' ? 'users' : 'restaurant-chains';
        return [
            'body' => $body,
            'contentType' => $mime . '; charset=UTF-8',
            'disposition' => $format === 'html' ? 'inline' : 'attachment; filename="' . $filename . '.' . $format . '"',
        ];
    }

    /** @param list<FileConvertible> $items */
    private static function htmlDocument(array $items, string $title): string
    {
        $sections = implode("\n", array_map(
            static fn(FileConvertible $item): string => $item->toHTML(), $items,
        ));
        $safeTitle = Html::escape($title);
        return '<!doctype html><html lang="en"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . $safeTitle . '</title><style>'
            . 'body{font-family:system-ui,Arial,sans-serif;background:#f3f6fa;color:#1f2937;margin:0}'
            . 'main{max-width:980px;margin:32px auto;background:white;padding:28px;border-radius:14px}'
            . '.chain{margin:2rem 0}.chain-card,.location{border:1px solid #d4dce8;border-radius:6px;padding:12px}'
            . '.location{margin:10px 0}.employee-row{padding:6px}.user-line{border-bottom:1px solid #ddd}'
            . 'summary{cursor:pointer;color:#0759b6}.company-facts div{display:flex;gap:12px;padding:5px}'
            . 'dt{font-weight:bold}dd{margin:0}a{color:#0759b6}'
            . '</style></head><body><main><p><a href="/generate.php">&larr; Back to form</a></p>'
            . '<h1>' . $safeTitle . '</h1>' . $sections . '</main></body></html>';
    }
}
