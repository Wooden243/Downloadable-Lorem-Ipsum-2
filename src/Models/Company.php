<?php
declare(strict_types=1);

namespace RestaurantMockup\Models;

use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Support\Html;
use RestaurantMockup\Support\RandomData;

class Company implements FileConvertible
{
    public function __construct(
        private string $name,
        private int $yearFounded,
        private string $description,
        private string $website,
        private string $phone,
        private string $industry,
        private string $ceo,
        private bool $publiclyTraded,
    ) {
        if (trim($name) === '' || $yearFounded < 1800 || $yearFounded > (int) date('Y')) {
            throw new \InvalidArgumentException('Invalid company name or foundation year.');
        }
        if (!filter_var($website, FILTER_VALIDATE_URL)
            || !in_array(strtolower((string) parse_url($website, PHP_URL_SCHEME)), ['https', 'http'], true)) {
            throw new \InvalidArgumentException('Company website must be an HTTP(S) URL.');
        }
    }

    public function getName(): string { return $this->name; }
    public function getYearFounded(): int { return $this->yearFounded; }
    public function getDescription(): string { return $this->description; }
    public function getWebsite(): string { return $this->website; }
    public function getPhone(): string { return $this->phone; }
    public function getIndustry(): string { return $this->industry; }
    public function getCeo(): string { return $this->ceo; }
    public function isPubliclyTraded(): bool { return $this->publiclyTraded; }

    public static function RandomGenerator(): self
    {
        $name = RandomData::companyName();
        return new self(
            $name, RandomData::yearFounded(),
            "{$name} operates hospitality and dining businesses.", RandomData::safeDomain($name),
            RandomData::phone(), RandomData::industry(), RandomData::fullName(), random_int(0, 1) === 1,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'yearFounded' => $this->yearFounded,
            'description' => $this->description,
            'website' => $this->website,
            'phone' => $this->phone,
            'industry' => $this->industry,
            'ceo' => $this->ceo,
            'publiclyTraded' => $this->publiclyTraded,
        ];
    }

    public function toString(): string
    {
        return sprintf(
            'Company: %s; Founded: %d; Description: %s; Website: %s; Phone: %s; Industry: %s; CEO: %s; Publicly traded: %s',
            $this->name, $this->yearFounded, $this->description, $this->website, $this->phone,
            $this->industry, $this->ceo, $this->publiclyTraded ? 'Yes' : 'No',
        );
    }

    public function toHTML(): string
    {
        $pairs = [
            'Company Name' => $this->name,
            'Year Founded' => (string) $this->yearFounded,
            'Description' => $this->description,
            'Phone' => $this->phone,
            'Industry' => $this->industry,
            'CEO' => $this->ceo,
            'Publicly Traded' => $this->publiclyTraded ? 'Yes' : 'No',
        ];
        $html = '<dl class="company-facts">';
        foreach ($pairs as $label => $value) {
            $html .= '<div><dt>' . Html::escape($label) . '</dt><dd>' . Html::escape($value) . '</dd></div>';
        }
        $url = Html::escape($this->website);
        return $html . '<div><dt>Website</dt><dd><a href="' . $url . '" rel="noopener noreferrer" target="_blank">'
            . $url . '</a></dd></div></dl>';
    }

    public function toMarkdown(): string
    {
        $data = [
            'Company Name' => $this->name,
            'Year Founded' => (string) $this->yearFounded,
            'Description' => $this->description,
            'Website' => $this->website,
            'Phone' => $this->phone,
            'Industry' => $this->industry,
            'CEO' => $this->ceo,
            'Publicly Traded' => $this->publiclyTraded ? 'Yes' : 'No',
        ];
        $out = '### Company Details' . "\n\n";
        foreach ($data as $field => $value) {
            $out .= '- **' . $field . '**: ' . Html::markdown($value) . "\n";
        }
        return $out;
    }
}
