# src/Models/RestaurantLocation.php — 全文・逐次解説

## ① ファイル概要

住所と開店状態、配下の従業員一覧を持つ店舗モデル。

**場所**：`src/Models/RestaurantLocation.php`。
**入出力と他ファイルの関係**：同一店舗に重複Employee IDを追加できない。従業員配列はtoArray/toHTML等へ展開される。

**前提知識**：店舗名・住所・ZIPは必須, addEmployeeで重複IDを拒否, Employee[]を持つ集約。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Models/RestaurantLocation.php` の全文で、生成時のSHA-256は `52892c5609f3154ddc4a476ebe62cb3529a089fd6c9bf8bf085bef9056e272a6`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Models;

use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Support\Html;
use RestaurantMockup\Support\RandomData;

class RestaurantLocation implements FileConvertible
{
    /** @var list<Employee> */
    private array $employees = [];

    public function __construct(
        private string $name,
        private string $address,
        private string $city,
        private string $state,
        private string $postalCode,
        private bool $isOpen,
    ) {
        if (trim($name) === '' || trim($address) === '' || trim($city) === '' || trim($state) === '' || trim($postalCode) === '') {
            throw new \InvalidArgumentException('Restaurant location fields may not be empty.');
        }
    }

    public function getName(): string { return $this->name; }
    public function getAddress(): string { return $this->address; }
    public function getCity(): string { return $this->city; }
    public function getState(): string { return $this->state; }
    public function getPostalCode(): string { return $this->postalCode; }
    public function isOpen(): bool { return $this->isOpen; }
    /** @return list<Employee> */
    public function getEmployees(): array { return $this->employees; }

    public function addEmployee(Employee $employee): void
    {
        foreach ($this->employees as $existing) {
            if ($existing->getId() === $employee->getId()) {
                throw new \InvalidArgumentException('Duplicate employee ID at this location.');
            }
        }
        $this->employees[] = $employee;
    }

    public static function RandomGenerator(): self
    {
        [$city, $state] = RandomData::location();
        $location = new self(
            RandomData::restaurantName(), RandomData::streetAddress(), $city, $state,
            RandomData::postalCode(), random_int(0, 9) !== 0,
        );
        for ($i = 0, $count = random_int(2, 5); $i < $count; $i++) {
            $employee = Employee::RandomGenerator();
            // Extremely rare random ID collision: retry instead of failing mock generation.
            $ids = array_map(static fn(Employee $existing): int => $existing->getId(), $location->employees);
            while (in_array($employee->getId(), $ids, true)) {
                $employee = Employee::RandomGenerator();
            }
            $location->addEmployee($employee);
        }
        return $location;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postalCode' => $this->postalCode,
            'isOpen' => $this->isOpen,
            'employees' => array_map(
                static fn(Employee $employee): array => $employee->toArray(),
                $this->employees,
            ),
        ];
    }

    public function toString(): string
    {
        $employeeLines = array_map(static fn(Employee $employee): string => '  ' . $employee->toString(), $this->employees);
        return sprintf(
            "Location: %s; Address: %s, %s, %s %s; Open: %s\nEmployees:\n%s",
            $this->name, $this->address, $this->city, $this->state, $this->postalCode,
            $this->isOpen ? 'Yes' : 'No', implode("\n", $employeeLines),
        );
    }

    public function toHTML(): string
    {
        $name = Html::escape($this->name);
        $address = Html::escape($this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $this->postalCode);
        $status = $this->isOpen ? 'Open' : 'Closed';
        $html = '<details class="location"><summary>' . $name . '</summary><div class="location-body">';
        $html .= '<p><strong>Address:</strong> ' . $address . '</p>';
        $html .= '<p><strong>Status:</strong> ' . $status . '</p>';
        $html .= '<h4>Employees (' . count($this->employees) . ')</h4><ul class="employee-list">';
        foreach ($this->employees as $employee) {
            $html .= $employee->toHTML();
        }
        return $html . '</ul></div></details>';
    }

    public function toMarkdown(): string
    {
        $out = '### ' . Html::markdown($this->name) . "\n\n";
        $out .= '- Address: ' . Html::markdown($this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $this->postalCode) . "\n";
        $out .= '- Open: ' . ($this->isOpen ? 'Yes' : 'No') . "\n";
        $out .= '- Employees (' . count($this->employees) . "): \n";
        foreach ($this->employees as $employee) {
            $out .= '  ' . $employee->toMarkdown() . "\n";
        }
        return $out . "\n";
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
| 10 | `class RestaurantLocation implements FileConvertible` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 12 | `/** @var list<Employee> */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 13 | `private array $employees = [];` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 14 | `(空行)` | 空行。論理的な段落の境界。 |
| 15 | `public function __construct(` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 16 | `private string $name,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 17 | `private string $address,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 18 | `private string $city,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 19 | `private string $state,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 20 | `private string $postalCode,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `private bool $isOpen,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 22 | `) {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 23 | `if (trim($name) === '' \|\| trim($address) === '' \|\| trim($city) === '' \|\| trim($state) === '' \|\| tri…` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 24 | `throw new \InvalidArgumentException('Restaurant location fields may not be empty.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 25 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 26 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 27 | `(空行)` | 空行。論理的な段落の境界。 |
| 28 | `public function getName(): string { return $this->name; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 29 | `public function getAddress(): string { return $this->address; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 30 | `public function getCity(): string { return $this->city; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `public function getState(): string { return $this->state; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 32 | `public function getPostalCode(): string { return $this->postalCode; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 33 | `public function isOpen(): bool { return $this->isOpen; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 34 | `/** @return list<Employee> */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 35 | `public function getEmployees(): array { return $this->employees; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 36 | `(空行)` | 空行。論理的な段落の境界。 |
| 37 | `public function addEmployee(Employee $employee): void` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 38 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 39 | `foreach ($this->employees as $existing) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 40 | `if ($existing->getId() === $employee->getId()) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `throw new \InvalidArgumentException('Duplicate employee ID at this location.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 42 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 43 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 44 | `$this->employees[] = $employee;` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 45 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 46 | `(空行)` | 空行。論理的な段落の境界。 |
| 47 | `public static function RandomGenerator(): self` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 48 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 49 | `[$city, $state] = RandomData::location();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 50 | `$location = new self(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `RandomData::restaurantName(), RandomData::streetAddress(), $city, $state,` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 52 | `RandomData::postalCode(), random_int(0, 9) !== 0,` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 53 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 54 | `for ($i = 0, $count = random_int(2, 5); $i < $count; $i++) {` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 55 | `$employee = Employee::RandomGenerator();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 56 | `// Extremely rare random ID collision: retry instead of failing mock generation.` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 57 | `$ids = array_map(static fn(Employee $existing): int => $existing->getId(), $location->employees);` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 58 | `while (in_array($employee->getId(), $ids, true)) {` | 許可された値の集合に含まれるか確認。第3引数trueなら型も厳密に比較。 |
| 59 | `$employee = Employee::RandomGenerator();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 60 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `$location->addEmployee($employee);` | Employeeオブジェクトを該当店舗の社員一覧に追加する。 |
| 62 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 63 | `return $location;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 64 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 65 | `(空行)` | 空行。論理的な段落の境界。 |
| 66 | `public function toArray(): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 67 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 68 | `return [` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 69 | `'name' => $this->name,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 70 | `'address' => $this->address,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 71〜80

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `'city' => $this->city,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 72 | `'state' => $this->state,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 73 | `'postalCode' => $this->postalCode,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 74 | `'isOpen' => $this->isOpen,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 75 | `'employees' => array_map(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 76 | `static fn(Employee $employee): array => $employee->toArray(),` | モデル属性をJSONへ変換しやすいPHP配列へ組み立てる。 |
| 77 | `$this->employees,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 78 | `),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 79 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 80 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 81〜90

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 81 | `(空行)` | 空行。論理的な段落の境界。 |
| 82 | `public function toString(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 83 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 84 | `$employeeLines = array_map(static fn(Employee $employee): string => '  ' . $employee->toString(), $this->em…` | モデルをテキスト表現に変換する。 |
| 85 | `return sprintf(` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 86 | `"Location: %s; Address: %s, %s, %s %s; Open: %s\nEmployees:\n%s",` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 87 | `$this->name, $this->address, $this->city, $this->state, $this->postalCode,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 88 | `$this->isOpen ? 'Yes' : 'No', implode("\n", $employeeLines),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 89 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 90 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 91〜100

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 91 | `(空行)` | 空行。論理的な段落の境界。 |
| 92 | `public function toHTML(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 93 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 94 | `$name = Html::escape($this->name);` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 95 | `$address = Html::escape($this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $this->postalCode);` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 96 | `$status = $this->isOpen ? 'Open' : 'Closed';` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 97 | `$html = '<details class="location"><summary>' . $name . '</summary><div class="location-body">';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 98 | `$html .= '<p><strong>Address:</strong> ' . $address . '</p>';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 99 | `$html .= '<p><strong>Status:</strong> ' . $status . '</p>';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 100 | `$html .= '<h4>Employees (' . count($this->employees) . ')</h4><ul class="employee-list">';` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 101〜110

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 101 | `foreach ($this->employees as $employee) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 102 | `$html .= $employee->toHTML();` | モデルからHTMLマークアップを生成するメソッド。出力値はescape済みであるべき。 |
| 103 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 104 | `return $html . '</ul></div></details>';` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 105 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 106 | `(空行)` | 空行。論理的な段落の境界。 |
| 107 | `public function toMarkdown(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 108 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 109 | `$out = '### ' . Html::markdown($this->name) . "\n\n";` | Markdown内の特殊文字や改行をエスケープする。 |
| 110 | `$out .= '- Address: ' . Html::markdown($this->address . ', ' . $this->city . ', ' . $this->state . ' ' . $t…` | Markdown内の特殊文字や改行をエスケープする。 |

### 行 111〜118

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 111 | `$out .= '- Open: ' . ($this->isOpen ? 'Yes' : 'No') . "\n";` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 112 | `$out .= '- Employees (' . count($this->employees) . "): \n";` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 113 | `foreach ($this->employees as $employee) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 114 | `$out .= '  ' . $employee->toMarkdown() . "\n";` | モデルをMarkdownの見出し・リストなどへ整形する。 |
| 115 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 116 | `return $out . "\n";` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 117 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 118 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

社員2人の店舗へaddEmployeeした後、toArray.employeesに2人の配列が返る。

## ⑤ 最重要部分の再確認

1. 店舗名・住所・ZIPは必須。
2. addEmployeeで重複IDを拒否。
3. Employee[]を持つ集約。
4. toArrayでemployeesリストを再帰変換。
5. ZIPは文字列として保存する。
6. HTMLのdetailsとsummaryで店舗情報を展開。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

