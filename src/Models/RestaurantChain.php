<?php
declare(strict_types=1);

namespace RestaurantMockup\Models;

use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Support\Html;
use RestaurantMockup\Support\RandomData;

class RestaurantChain extends Company implements FileConvertible
{
    /** @var list<RestaurantLocation> */
    private array $restaurantLocations = [];

    public function __construct(
        string $name,
        int $yearFounded,
        string $description,
        string $website,
        string $phone,
        string $industry,
        string $ceo,
        bool $publiclyTraded,
        private int $chainId,
        private string $cuisineType,
        private bool $hasDriveThru,
        private string $parentCompany,
    ) {
        parent::__construct($name, $yearFounded, $description, $website, $phone, $industry, $ceo, $publiclyTraded);
        if ($chainId <= 0 || trim($cuisineType) === '') {
            throw new \InvalidArgumentException('Chain ID and cuisine type are required.');
        }
    }

    public function getChainId(): int { return $this->chainId; }
    public function getCuisineType(): string { return $this->cuisineType; }
    public function hasDriveThru(): bool { return $this->hasDriveThru; }
    public function getParentCompany(): string { return $this->parentCompany; }
    public function getNumberOfLocations(): int { return count($this->restaurantLocations); }
    /** @return list<RestaurantLocation> */
    public function getRestaurantLocations(): array { return $this->restaurantLocations; }

    public function addLocation(RestaurantLocation $location): void
    {
        $this->restaurantLocations[] = $location;
    }

    public static function RandomGenerator(): self
    {
        $company = Company::RandomGenerator();
        $chain = new self(
            $company->getName(), $company->getYearFounded(), $company->getDescription(),
            $company->getWebsite(), $company->getPhone(), $company->getIndustry(),
            $company->getCeo(), $company->isPubliclyTraded(),
            random_int(100, 99999), RandomData::cuisine(), random_int(0, 1) === 1,
            random_int(0, 3) === 0 ? RandomData::companyName() : 'Independent',
        );
        for ($i = 0, $count = random_int(2, 4); $i < $count; $i++) {
            $chain->addLocation(RestaurantLocation::RandomGenerator());
        }
        return $chain;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'chainId' => $this->chainId,
            'cuisineType' => $this->cuisineType,
            'hasDriveThru' => $this->hasDriveThru,
            'parentCompany' => $this->parentCompany,
            'numberOfLocations' => count($this->restaurantLocations),
            'restaurantLocations' => array_map(
                static fn(RestaurantLocation $location): array => $location->toArray(),
                $this->restaurantLocations,
            ),
        ]);
    }

    public function toString(): string
    {
        $locations = array_map(static fn(RestaurantLocation $location): string => $location->toString(), $this->restaurantLocations);
        return sprintf(
            "Restaurant Chain #%d: %s; Cuisine: %s; Locations: %d; Drive-thru: %s; Parent Company: %s\n%s\n%s",
            $this->chainId, $this->getName(), $this->cuisineType, $this->getNumberOfLocations(),
            $this->hasDriveThru ? 'Yes' : 'No', $this->parentCompany, parent::toString(), implode("\n", $locations),
        );
    }

    public function toHTML(): string
    {
        $html = '<section class="chain"><h2>Restaurant Chain ' . Html::escape($this->getName()) . '</h2>';
        $html .= '<div class="chain-card"><div class="panel-heading">Restaurant Chain Information</div><div class="chain-content">';
        $html .= '<div class="chain-metadata">Chain ID: ' . $this->chainId
            . ' · Cuisine: ' . Html::escape($this->cuisineType)
            . ' · Locations: ' . $this->getNumberOfLocations()
            . ' · Drive-thru: ' . ($this->hasDriveThru ? 'Yes' : 'No')
            . ' · Parent: ' . Html::escape($this->parentCompany) . '</div>';
        foreach ($this->restaurantLocations as $location) {
            $html .= $location->toHTML();
        }
        $html .= '<details class="company-details"><summary>Company Details</summary><div class="company-body">';
        $html .= parent::toHTML();
        return $html . '</div></details></div></div></section>';
    }

    public function toMarkdown(): string
    {
        $out = '## Restaurant Chain ' . Html::markdown($this->getName()) . "\n\n";
        $out .= '- Chain ID: ' . $this->chainId . "\n";
        $out .= '- Cuisine: ' . Html::markdown($this->cuisineType) . "\n";
        $out .= '- Locations: ' . $this->getNumberOfLocations() . "\n";
        $out .= '- Drive-thru: ' . ($this->hasDriveThru ? 'Yes' : 'No') . "\n";
        $out .= '- Parent company: ' . Html::markdown($this->parentCompany) . "\n\n";
        $out .= parent::toMarkdown() . "\n";
        foreach ($this->restaurantLocations as $location) {
            $out .= $location->toMarkdown();
        }
        return $out;
    }
}
