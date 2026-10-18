<?php
declare(strict_types=1);

namespace RestaurantMockup\Contracts;

/** A common contract for plain text, HTML and Markdown output. */
interface FileConvertible
{
    public function toString(): string;
    public function toHTML(): string;
    public function toMarkdown(): string;
}
