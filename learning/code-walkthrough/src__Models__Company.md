# src/Models/Company.php — 全文・逐次解説

## ① ファイル概要

レストランチェーンが継承する会社共通属性を保持する。

**場所**：`src/Models/Company.php`。
**入出力と他ファイルの関係**：社名、設立年、URL等を検証し、テキスト・HTML・Markdown・配列へ変換する。

**前提知識**：社名と設立年を検証する, websiteをURLとして検証する, URLスキームはhttp/httpsだけ。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Models/Company.php` の全文で、生成時のSHA-256は `e7a877341e16cbc0051217c0e17251cb8b69dc26c9d5b2695bcf83ea6ffc2878`。

```php
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
| 10 | `class Company implements FileConvertible` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 12 | `public function __construct(` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 13 | `private string $name,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 14 | `private int $yearFounded,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 15 | `private string $description,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 16 | `private string $website,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 17 | `private string $phone,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 18 | `private string $industry,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 19 | `private string $ceo,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 20 | `private bool $publiclyTraded,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `) {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 22 | `if (trim($name) === '' \|\| $yearFounded < 1800 \|\| $yearFounded > (int) date('Y')) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 23 | `throw new \InvalidArgumentException('Invalid company name or foundation year.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 24 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 25 | `if (!filter_var($website, FILTER_VALIDATE_URL)` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 26 | `\|\| !in_array(strtolower((string) parse_url($website, PHP_URL_SCHEME)), ['https', 'http'], true)) {` | 許可された値の集合に含まれるか確認。第3引数trueなら型も厳密に比較。 |
| 27 | `throw new \InvalidArgumentException('Company website must be an HTTP(S) URL.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 28 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 29 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 30 | `(空行)` | 空行。論理的な段落の境界。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `public function getName(): string { return $this->name; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 32 | `public function getYearFounded(): int { return $this->yearFounded; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 33 | `public function getDescription(): string { return $this->description; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 34 | `public function getWebsite(): string { return $this->website; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 35 | `public function getPhone(): string { return $this->phone; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 36 | `public function getIndustry(): string { return $this->industry; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 37 | `public function getCeo(): string { return $this->ceo; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 38 | `public function isPubliclyTraded(): bool { return $this->publiclyTraded; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 39 | `(空行)` | 空行。論理的な段落の境界。 |
| 40 | `public static function RandomGenerator(): self` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 42 | `$name = RandomData::companyName();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 43 | `return new self(` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 44 | `$name, RandomData::yearFounded(),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 45 | `"{$name} operates hospitality and dining businesses.", RandomData::safeDomain($name),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 46 | `RandomData::phone(), RandomData::industry(), RandomData::fullName(), random_int(0, 1) === 1,` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 47 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 48 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 49 | `(空行)` | 空行。論理的な段落の境界。 |
| 50 | `public function toArray(): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 52 | `return [` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 53 | `'name' => $this->name,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 54 | `'yearFounded' => $this->yearFounded,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 55 | `'description' => $this->description,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 56 | `'website' => $this->website,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 57 | `'phone' => $this->phone,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 58 | `'industry' => $this->industry,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 59 | `'ceo' => $this->ceo,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 60 | `'publiclyTraded' => $this->publiclyTraded,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 62 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 63 | `(空行)` | 空行。論理的な段落の境界。 |
| 64 | `public function toString(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 65 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 66 | `return sprintf(` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 67 | `'Company: %s; Founded: %d; Description: %s; Website: %s; Phone: %s; Industry: %s; CEO: %s; Publicly traded:…` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 68 | `$this->name, $this->yearFounded, $this->description, $this->website, $this->phone,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 69 | `$this->industry, $this->ceo, $this->publiclyTraded ? 'Yes' : 'No',` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 70 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 71〜80

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 72 | `(空行)` | 空行。論理的な段落の境界。 |
| 73 | `public function toHTML(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 74 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 75 | `$pairs = [` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 76 | `'Company Name' => $this->name,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 77 | `'Year Founded' => (string) $this->yearFounded,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 78 | `'Description' => $this->description,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 79 | `'Phone' => $this->phone,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 80 | `'Industry' => $this->industry,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 81〜90

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 81 | `'CEO' => $this->ceo,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 82 | `'Publicly Traded' => $this->publiclyTraded ? 'Yes' : 'No',` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 83 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 84 | `$html = '<dl class="company-facts">';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 85 | `foreach ($pairs as $label => $value) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 86 | `$html .= '<div><dt>' . Html::escape($label) . '</dt><dd>' . Html::escape($value) . '</dd></div>';` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 87 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 88 | `$url = Html::escape($this->website);` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 89 | `return $html . '<div><dt>Website</dt><dd><a href="' . $url . '" rel="noopener noreferrer" target="_blank">'` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 90 | `. $url . '</a></dd></div></dl>';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 91〜100

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 91 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 92 | `(空行)` | 空行。論理的な段落の境界。 |
| 93 | `public function toMarkdown(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 94 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 95 | `$data = [` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 96 | `'Company Name' => $this->name,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 97 | `'Year Founded' => (string) $this->yearFounded,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 98 | `'Description' => $this->description,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 99 | `'Website' => $this->website,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 100 | `'Phone' => $this->phone,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 101〜110

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 101 | `'Industry' => $this->industry,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 102 | `'CEO' => $this->ceo,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 103 | `'Publicly Traded' => $this->publiclyTraded ? 'Yes' : 'No',` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 104 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 105 | `$out = '### Company Details' . "\n\n";` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 106 | `foreach ($data as $field => $value) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 107 | `$out .= '- **' . $field . '**: ' . Html::markdown($value) . "\n";` | Markdown内の特殊文字や改行をエスケープする。 |
| 108 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 109 | `return $out;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 110 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 111〜111

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 111 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

RestaurantChainのparent::toHTMLにより会社情報パネルが作られる。

## ⑤ 最重要部分の再確認

1. 社名と設立年を検証する。
2. websiteをURLとして検証する。
3. URLスキームはhttp/httpsだけ。
4. toArrayは会社属性の名前を保持。
5. HTMLはラベルと値のペアをdlで表す。
6. 外部リンク文字列をescapeする。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

