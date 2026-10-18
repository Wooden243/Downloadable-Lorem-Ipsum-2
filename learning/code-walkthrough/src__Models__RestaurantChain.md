# src/Models/RestaurantChain.php — 全文・逐次解説

## ① ファイル概要

Companyを継承し、店舗オブジェクトの配列とチェーン固有の属性を持つ。

**場所**：`src/Models/RestaurantChain.php`。
**入出力と他ファイルの関係**：addLocationで構成要素を保持する。toArrayはネストされた店舗データを配列化する。

**前提知識**：RestaurantChain extends Company, 店舗を配列で保持する構成関係, getNumberOfLocationsは配列長を返す。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Models/RestaurantChain.php` の全文で、生成時のSHA-256は `18e4c2dcb6dee4bff86162d2ced29071b1c0a7d9c84e2e36d4b7e69b38e5b7a7`。

```php
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
```

## ③ 上から下へ — 逐次読解

**行番号は上記ソースコードの行番号に一致する。** 各行の構文・必要性・変更時の影響を確認する。

### 行 1〜10

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 1 | `<?php` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 2 | `declare(strict_types=1);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 3 | `(空行)` | 空行。論理的な段落の境界。 |
| 4 | `namespace RestaurantMockup\Models;` | 名前空間の宣言。クラス名衝突を防ぎ、autoloadのパス対応を決める。 |
| 5 | `(空行)` | 空行。論理的な段落の境界。 |
| 6 | `use RestaurantMockup\Contracts\FileConvertible;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `use RestaurantMockup\Support\Html;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 8 | `use RestaurantMockup\Support\RandomData;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 9 | `(空行)` | 空行。論理的な段落の境界。 |
| 10 | `class RestaurantChain extends Company implements FileConvertible` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 12 | `/** @var list<RestaurantLocation> */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 13 | `private array $restaurantLocations = [];` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 14 | `(空行)` | 空行。論理的な段落の境界。 |
| 15 | `public function __construct(` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 16 | `string $name,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 17 | `int $yearFounded,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 18 | `string $description,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 19 | `string $website,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 20 | `string $phone,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `string $industry,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 22 | `string $ceo,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 23 | `bool $publiclyTraded,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 24 | `private int $chainId,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 25 | `private string $cuisineType,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 26 | `private bool $hasDriveThru,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 27 | `private string $parentCompany,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 28 | `) {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 29 | `parent::__construct($name, $yearFounded, $description, $website, $phone, $industry, $ceo, $publiclyTraded);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 30 | `if ($chainId <= 0 \|\| trim($cuisineType) === '') {` | 条件分岐。この条件を満たすときだけ処理を進める。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `throw new \InvalidArgumentException('Chain ID and cuisine type are required.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 32 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 33 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 34 | `(空行)` | 空行。論理的な段落の境界。 |
| 35 | `public function getChainId(): int { return $this->chainId; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 36 | `public function getCuisineType(): string { return $this->cuisineType; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 37 | `public function hasDriveThru(): bool { return $this->hasDriveThru; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 38 | `public function getParentCompany(): string { return $this->parentCompany; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 39 | `public function getNumberOfLocations(): int { return count($this->restaurantLocations); }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 40 | `/** @return list<RestaurantLocation> */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `public function getRestaurantLocations(): array { return $this->restaurantLocations; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 42 | `(空行)` | 空行。論理的な段落の境界。 |
| 43 | `public function addLocation(RestaurantLocation $location): void` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 44 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 45 | `$this->restaurantLocations[] = $location;` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 46 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 47 | `(空行)` | 空行。論理的な段落の境界。 |
| 48 | `public static function RandomGenerator(): self` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 49 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 50 | `$company = Company::RandomGenerator();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `$chain = new self(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 52 | `$company->getName(), $company->getYearFounded(), $company->getDescription(),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 53 | `$company->getWebsite(), $company->getPhone(), $company->getIndustry(),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 54 | `$company->getCeo(), $company->isPubliclyTraded(),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 55 | `random_int(100, 99999), RandomData::cuisine(), random_int(0, 1) === 1,` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 56 | `random_int(0, 3) === 0 ? RandomData::companyName() : 'Independent',` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 57 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 58 | `for ($i = 0, $count = random_int(2, 4); $i < $count; $i++) {` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 59 | `$chain->addLocation(RestaurantLocation::RandomGenerator());` | RestaurantLocationをRestaurantChainの店舗一覧に登録する。 |
| 60 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `return $chain;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 62 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 63 | `(空行)` | 空行。論理的な段落の境界。 |
| 64 | `public function toArray(): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 65 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 66 | `return array_merge(parent::toArray(), [` | モデル属性をJSONへ変換しやすいPHP配列へ組み立てる。 |
| 67 | `'chainId' => $this->chainId,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 68 | `'cuisineType' => $this->cuisineType,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 69 | `'hasDriveThru' => $this->hasDriveThru,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 70 | `'parentCompany' => $this->parentCompany,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 71〜80

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `'numberOfLocations' => count($this->restaurantLocations),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 72 | `'restaurantLocations' => array_map(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 73 | `static fn(RestaurantLocation $location): array => $location->toArray(),` | モデル属性をJSONへ変換しやすいPHP配列へ組み立てる。 |
| 74 | `$this->restaurantLocations,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 75 | `),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 76 | `]);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 77 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 78 | `(空行)` | 空行。論理的な段落の境界。 |
| 79 | `public function toString(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 80 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 81〜90

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 81 | `$locations = array_map(static fn(RestaurantLocation $location): string => $location->toString(), $this->res…` | モデルをテキスト表現に変換する。 |
| 82 | `return sprintf(` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 83 | `"Restaurant Chain #%d: %s; Cuisine: %s; Locations: %d; Drive-thru: %s; Parent Company: %s\n%s\n%s",` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 84 | `$this->chainId, $this->getName(), $this->cuisineType, $this->getNumberOfLocations(),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 85 | `$this->hasDriveThru ? 'Yes' : 'No', $this->parentCompany, parent::toString(), implode("\n", $locations),` | モデルをテキスト表現に変換する。 |
| 86 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 87 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 88 | `(空行)` | 空行。論理的な段落の境界。 |
| 89 | `public function toHTML(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 90 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 91〜100

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 91 | `$html = '<section class="chain"><h2>Restaurant Chain ' . Html::escape($this->getName()) . '</h2>';` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 92 | `$html .= '<div class="chain-card"><div class="panel-heading">Restaurant Chain Information</div><div class="…` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 93 | `$html .= '<div class="chain-metadata">Chain ID: ' . $this->chainId` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 94 | `. ' · Cuisine: ' . Html::escape($this->cuisineType)` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 95 | `. ' · Locations: ' . $this->getNumberOfLocations()` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 96 | `. ' · Drive-thru: ' . ($this->hasDriveThru ? 'Yes' : 'No')` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 97 | `. ' · Parent: ' . Html::escape($this->parentCompany) . '</div>';` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 98 | `foreach ($this->restaurantLocations as $location) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 99 | `$html .= $location->toHTML();` | モデルからHTMLマークアップを生成するメソッド。出力値はescape済みであるべき。 |
| 100 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 101〜110

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 101 | `$html .= '<details class="company-details"><summary>Company Details</summary><div class="company-body">';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 102 | `$html .= parent::toHTML();` | モデルからHTMLマークアップを生成するメソッド。出力値はescape済みであるべき。 |
| 103 | `return $html . '</div></details></div></div></section>';` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 104 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 105 | `(空行)` | 空行。論理的な段落の境界。 |
| 106 | `public function toMarkdown(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 107 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 108 | `$out = '## Restaurant Chain ' . Html::markdown($this->getName()) . "\n\n";` | Markdown内の特殊文字や改行をエスケープする。 |
| 109 | `$out .= '- Chain ID: ' . $this->chainId . "\n";` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 110 | `$out .= '- Cuisine: ' . Html::markdown($this->cuisineType) . "\n";` | Markdown内の特殊文字や改行をエスケープする。 |

### 行 111〜120

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 111 | `$out .= '- Locations: ' . $this->getNumberOfLocations() . "\n";` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 112 | `$out .= '- Drive-thru: ' . ($this->hasDriveThru ? 'Yes' : 'No') . "\n";` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 113 | `$out .= '- Parent company: ' . Html::markdown($this->parentCompany) . "\n\n";` | Markdown内の特殊文字や改行をエスケープする。 |
| 114 | `$out .= parent::toMarkdown() . "\n";` | モデルをMarkdownの見出し・リストなどへ整形する。 |
| 115 | `foreach ($this->restaurantLocations as $location) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 116 | `$out .= $location->toMarkdown();` | モデルをMarkdownの見出し・リストなどへ整形する。 |
| 117 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 118 | `return $out;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 119 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 120 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

3店舗ならgetNumberOfLocations()===3。toHTMLでは各店舗をdetails要素として表示する。

## ⑤ 最重要部分の再確認

1. RestaurantChain extends Company。
2. 店舗を配列で保持する構成関係。
3. getNumberOfLocationsは配列長を返す。
4. toArrayが各RestaurantLocationのtoArrayを再帰利用。
5. toHTMLは折りたたみUIを出力する。
6. parent::toHTMLで会社情報を付加。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

