<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

final class Html
{
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Escape characters that have structural meaning in Markdown tables. */
    public static function markdown(string $value): string
    {
        return str_replace(["\\", '|', "\r", "\n"], ['\\\\', '\\|', '', ' '], $value);
    }
}
