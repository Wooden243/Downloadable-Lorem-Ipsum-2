# src/Models/Employee.php — 全文・逐次解説

## ① ファイル概要

Userに職種・給与・開始日・職務リストを追加する従業員モデル。

**場所**：`src/Models/Employee.php`。
**入出力と他ファイルの関係**：parent::__constructでUserのフィールドを検証。toArrayは親の配列に追加フィールドをマージする。

**前提知識**：継承 extends User, 親クラスのコンストラクタ再利用, 給与は非負有限値で検証。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Models/Employee.php` の全文で、生成時のSHA-256は `c3854d2e791fbf850f823b07816423cbe015e5bbb1d8c61061be661173734844`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Models;

use DateTimeImmutable;
use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Support\Html;
use RestaurantMockup\Support\RandomData;

class Employee extends User implements FileConvertible
{
    /** @param list<string> $roles */
    public function __construct(
        int $id,
        string $name,
        string $email,
        private string $jobTitle,
        private float $salary,
        private DateTimeImmutable $startDate,
        private array $roles,
    ) {
        parent::__construct($id, $name, $email);
        if (trim($jobTitle) === '' || !is_finite($salary) || $salary < 0) {
            throw new \InvalidArgumentException('Job title and nonnegative finite salary are required.');
        }
        foreach ($roles as $role) {
            if (!is_string($role) || trim($role) === '') {
                throw new \InvalidArgumentException('Each employee role must be a nonempty string.');
            }
        }
    }

    public function getJobTitle(): string { return $this->jobTitle; }
    public function getSalary(): float { return $this->salary; }
    public function getStartDate(): DateTimeImmutable { return $this->startDate; }
    /** @return list<string> */
    public function getRoles(): array { return $this->roles; }

    public static function RandomGenerator(): self
    {
        $id = random_int(1000, 999999);
        $title = RandomData::jobTitle();
        return new self(
            $id, RandomData::fullName(), "employee{$id}@example.com",
            $title, (float) random_int(32000, 95000), RandomData::startDate(),
            [$title, 'Guest service'],
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'jobTitle' => $this->jobTitle,
            'salary' => $this->salary,
            'startDate' => $this->startDate->format('Y-m-d'),
            'roles' => $this->roles,
        ]);
    }

    public function toString(): string
    {
        return sprintf(
            'ID: %d; Job Title: %s; Name: %s; Salary: $%s; Start Date: %s; Roles: %s',
            $this->getId(), $this->jobTitle, $this->getName(),
            number_format($this->salary, 2), $this->startDate->format('Y-m-d'), implode(', ', $this->roles),
        );
    }

    public function toHTML(): string
    {
        return '<li class="employee-row">' . Html::escape($this->toString()) . '</li>';
    }

    public function toMarkdown(): string
    {
        return '- ' . Html::markdown($this->toString());
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
| 6 | `use DateTimeImmutable;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `use RestaurantMockup\Contracts\FileConvertible;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 8 | `use RestaurantMockup\Support\Html;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 9 | `use RestaurantMockup\Support\RandomData;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 10 | `(空行)` | 空行。論理的な段落の境界。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `class Employee extends User implements FileConvertible` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |
| 12 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 13 | `/** @param list<string> $roles */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 14 | `public function __construct(` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 15 | `int $id,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 16 | `string $name,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 17 | `string $email,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 18 | `private string $jobTitle,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 19 | `private float $salary,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 20 | `private DateTimeImmutable $startDate,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `private array $roles,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 22 | `) {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 23 | `parent::__construct($id, $name, $email);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 24 | `if (trim($jobTitle) === '' \|\| !is_finite($salary) \|\| $salary < 0) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 25 | `throw new \InvalidArgumentException('Job title and nonnegative finite salary are required.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 26 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 27 | `foreach ($roles as $role) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 28 | `if (!is_string($role) \|\| trim($role) === '') {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 29 | `throw new \InvalidArgumentException('Each employee role must be a nonempty string.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 30 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 32 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 33 | `(空行)` | 空行。論理的な段落の境界。 |
| 34 | `public function getJobTitle(): string { return $this->jobTitle; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 35 | `public function getSalary(): float { return $this->salary; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 36 | `public function getStartDate(): DateTimeImmutable { return $this->startDate; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 37 | `/** @return list<string> */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 38 | `public function getRoles(): array { return $this->roles; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 39 | `(空行)` | 空行。論理的な段落の境界。 |
| 40 | `public static function RandomGenerator(): self` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 42 | `$id = random_int(1000, 999999);` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 43 | `$title = RandomData::jobTitle();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 44 | `return new self(` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 45 | `$id, RandomData::fullName(), "employee{$id}@example.com",` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 46 | `$title, (float) random_int(32000, 95000), RandomData::startDate(),` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 47 | `[$title, 'Guest service'],` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 48 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 49 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 50 | `(空行)` | 空行。論理的な段落の境界。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `public function toArray(): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 52 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 53 | `return array_merge(parent::toArray(), [` | モデル属性をJSONへ変換しやすいPHP配列へ組み立てる。 |
| 54 | `'jobTitle' => $this->jobTitle,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 55 | `'salary' => $this->salary,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 56 | `'startDate' => $this->startDate->format('Y-m-d'),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 57 | `'roles' => $this->roles,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 58 | `]);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 59 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 60 | `(空行)` | 空行。論理的な段落の境界。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `public function toString(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 62 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 63 | `return sprintf(` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 64 | `'ID: %d; Job Title: %s; Name: %s; Salary: $%s; Start Date: %s; Roles: %s',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 65 | `$this->getId(), $this->jobTitle, $this->getName(),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 66 | `number_format($this->salary, 2), $this->startDate->format('Y-m-d'), implode(', ', $this->roles),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 67 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 68 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 69 | `(空行)` | 空行。論理的な段落の境界。 |
| 70 | `public function toHTML(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |

### 行 71〜79

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 72 | `return '<li class="employee-row">' . Html::escape($this->toString()) . '</li>';` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 73 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 74 | `(空行)` | 空行。論理的な段落の境界。 |
| 75 | `public function toMarkdown(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 76 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 77 | `return '- ' . Html::markdown($this->toString());` | Markdown内の特殊文字や改行をエスケープする。 |
| 78 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 79 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

年収45000のEmployeeのtoArray.salaryは45000、toStringにはJob Title等が入る。

## ⑤ 最重要部分の再確認

1. 継承 extends User。
2. 親クラスのコンストラクタ再利用。
3. 給与は非負有限値で検証。
4. 開始日はDateTimeImmutable。
5. toArrayはparent::toArrayを起点にする。
6. toStringが年収と日付を人に読める形式へ。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

