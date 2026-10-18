# src/Support/RandomData.php — 全文・逐次解説

## ① ファイル概要

ダミーの名前、都市、電話番号、会社名などの小さな語彙集と無作為選択関数を集約する。

**場所**：`src/Support/RandomData.php`。
**入出力と他ファイルの関係**：random_intにより外部API不要で実例用文字列を作る。実在個人のレコードには接続しない。

**前提知識**：候補一覧を定数に閉じ込める, pickは空配列を拒否, random_intは両端を含む。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Support/RandomData.php` の全文で、生成時のSHA-256は `1cee1bde7353802c18cd6556d927ddd4b2916cd79b829b64890034155dc014f0`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

use DateTimeImmutable;

/** Small self-contained fake-data vocabulary; no external Faker dependency. */
final class RandomData
{
    private const FIRST = ['Avery','Jordan','Morgan','Taylor','Riley','Casey','Alex','Jamie','Robin','Drew','Parker','Quinn','Noah','Harper','Emery'];
    private const LAST = ['Reed','Martin','Patel','Kim','Santos','Brooks','Murphy','Chen','Taylor','Rivera','Cooper','Bennett','Lewis','Carter','Hayes'];
    private const CITY_STATES = [
        ['Seattle','WA'], ['Austin','TX'], ['Denver','CO'], ['Portland','OR'],
        ['Chicago','IL'], ['Boston','MA'], ['Phoenix','AZ'], ['Raleigh','NC'],
        ['San Diego','CA'], ['Nashville','TN'], ['Atlanta','GA'], ['Madison','WI'],
    ];
    private const STREET = ['Maple','Oak','Pine','Cedar','Union','Sunset','Broadway','Market','Willow','Lake'];
    private const CUISINES = ['Italian','Japanese','Mexican','American','Mediterranean','Thai','Indian'];
    private const COMPANY_PARTS = ['Harbor','Silver','Maple','Bluebird','Summit','River','Evergreen','Golden','Clover','Northstar'];
    private const JOBS = ['Cashier','Chef','Server','Shift Manager','Host','Line Cook','General Manager'];
    private const INDUSTRIES = ['Food & Beverage','Hospitality','Restaurant Operations'];

    public static function pick(array $values): mixed
    {
        if ($values === []) {
            throw new \InvalidArgumentException('Cannot pick from an empty array.');
        }
        return $values[random_int(0, count($values) - 1)];
    }

    public static function fullName(): string
    {
        return self::pick(self::FIRST) . ' ' . self::pick(self::LAST);
    }

    public static function companyName(): string
    {
        return self::pick(self::COMPANY_PARTS) . ' ' . self::pick(['Hospitality Group', 'Foods Inc.', 'Dining Co.', 'Restaurants LLC']);
    }

    public static function restaurantName(): string
    {
        return self::pick(self::COMPANY_PARTS) . ' ' . self::pick(['Kitchen','Bistro','Grill','Table','Cafe','Corner']);
    }

    public static function location(): array
    {
        return self::pick(self::CITY_STATES);
    }

    public static function streetAddress(): string
    {
        return random_int(10, 9800) . ' ' . self::pick(self::STREET) . ' ' . self::pick(['St','Ave','Blvd','Road']);
    }

    public static function postalCode(): string
    {
        return str_pad((string) random_int(1000, 99999), 5, '0', STR_PAD_LEFT);
    }

    public static function phone(): string
    {
        return sprintf('(%03d) %03d-%04d', random_int(201, 989), random_int(200, 999), random_int(0, 9999));
    }

    public static function cuisine(): string
    {
        return self::pick(self::CUISINES);
    }

    public static function jobTitle(): string
    {
        return self::pick(self::JOBS);
    }

    public static function industry(): string
    {
        return self::pick(self::INDUSTRIES);
    }

    public static function startDate(): DateTimeImmutable
    {
        $yearsAgo = random_int(0, 10);
        $daysAgo = random_int(0, 364);
        return (new DateTimeImmutable('today'))->modify("-{$yearsAgo} years")->modify("-{$daysAgo} days");
    }

    public static function yearFounded(): int
    {
        return random_int(1980, (int) date('Y'));
    }

    public static function safeDomain(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)) ?? 'restaurant');
        return 'https://' . trim($slug, '-') . '.example.com';
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
| 4 | `namespace RestaurantMockup\Support;` | 名前空間の宣言。クラス名衝突を防ぎ、autoloadのパス対応を決める。 |
| 5 | `(空行)` | 空行。論理的な段落の境界。 |
| 6 | `use DateTimeImmutable;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `(空行)` | 空行。論理的な段落の境界。 |
| 8 | `/** Small self-contained fake-data vocabulary; no external Faker dependency. */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 9 | `final class RandomData` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |
| 10 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `private const FIRST = ['Avery','Jordan','Morgan','Taylor','Riley','Casey','Alex','Jamie','Robin','Drew','Pa…` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 12 | `private const LAST = ['Reed','Martin','Patel','Kim','Santos','Brooks','Murphy','Chen','Taylor','Rivera','Co…` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 13 | `private const CITY_STATES = [` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 14 | `['Seattle','WA'], ['Austin','TX'], ['Denver','CO'], ['Portland','OR'],` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 15 | `['Chicago','IL'], ['Boston','MA'], ['Phoenix','AZ'], ['Raleigh','NC'],` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 16 | `['San Diego','CA'], ['Nashville','TN'], ['Atlanta','GA'], ['Madison','WI'],` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 17 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 18 | `private const STREET = ['Maple','Oak','Pine','Cedar','Union','Sunset','Broadway','Market','Willow','Lake'];` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 19 | `private const CUISINES = ['Italian','Japanese','Mexican','American','Mediterranean','Thai','Indian'];` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 20 | `private const COMPANY_PARTS = ['Harbor','Silver','Maple','Bluebird','Summit','River','Evergreen','Golden','…` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `private const JOBS = ['Cashier','Chef','Server','Shift Manager','Host','Line Cook','General Manager'];` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 22 | `private const INDUSTRIES = ['Food & Beverage','Hospitality','Restaurant Operations'];` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 23 | `(空行)` | 空行。論理的な段落の境界。 |
| 24 | `public static function pick(array $values): mixed` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 25 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 26 | `if ($values === []) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 27 | `throw new \InvalidArgumentException('Cannot pick from an empty array.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 28 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 29 | `return $values[random_int(0, count($values) - 1)];` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 30 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `(空行)` | 空行。論理的な段落の境界。 |
| 32 | `public static function fullName(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 33 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 34 | `return self::pick(self::FIRST) . ' ' . self::pick(self::LAST);` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 35 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 36 | `(空行)` | 空行。論理的な段落の境界。 |
| 37 | `public static function companyName(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 38 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 39 | `return self::pick(self::COMPANY_PARTS) . ' ' . self::pick(['Hospitality Group', 'Foods Inc.', 'Dining Co.',…` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 40 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `(空行)` | 空行。論理的な段落の境界。 |
| 42 | `public static function restaurantName(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 43 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 44 | `return self::pick(self::COMPANY_PARTS) . ' ' . self::pick(['Kitchen','Bistro','Grill','Table','Cafe','Corne…` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 45 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 46 | `(空行)` | 空行。論理的な段落の境界。 |
| 47 | `public static function location(): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 48 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 49 | `return self::pick(self::CITY_STATES);` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 50 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `(空行)` | 空行。論理的な段落の境界。 |
| 52 | `public static function streetAddress(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 53 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 54 | `return random_int(10, 9800) . ' ' . self::pick(self::STREET) . ' ' . self::pick(['St','Ave','Blvd','Road']);` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 55 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 56 | `(空行)` | 空行。論理的な段落の境界。 |
| 57 | `public static function postalCode(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 58 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 59 | `return str_pad((string) random_int(1000, 99999), 5, '0', STR_PAD_LEFT);` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 60 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `(空行)` | 空行。論理的な段落の境界。 |
| 62 | `public static function phone(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 63 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 64 | `return sprintf('(%03d) %03d-%04d', random_int(201, 989), random_int(200, 999), random_int(0, 9999));` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 65 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 66 | `(空行)` | 空行。論理的な段落の境界。 |
| 67 | `public static function cuisine(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 68 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 69 | `return self::pick(self::CUISINES);` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 70 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 71〜80

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `(空行)` | 空行。論理的な段落の境界。 |
| 72 | `public static function jobTitle(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 73 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 74 | `return self::pick(self::JOBS);` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 75 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 76 | `(空行)` | 空行。論理的な段落の境界。 |
| 77 | `public static function industry(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 78 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 79 | `return self::pick(self::INDUSTRIES);` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 80 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 81〜90

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 81 | `(空行)` | 空行。論理的な段落の境界。 |
| 82 | `public static function startDate(): DateTimeImmutable` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 83 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 84 | `$yearsAgo = random_int(0, 10);` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 85 | `$daysAgo = random_int(0, 364);` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 86 | `return (new DateTimeImmutable('today'))->modify("-{$yearsAgo} years")->modify("-{$daysAgo} days");` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 87 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 88 | `(空行)` | 空行。論理的な段落の境界。 |
| 89 | `public static function yearFounded(): int` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 90 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 91〜99

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 91 | `return random_int(1980, (int) date('Y'));` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 92 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 93 | `(空行)` | 空行。論理的な段落の境界。 |
| 94 | `public static function safeDomain(string $name): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 95 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 96 | `$slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)) ?? 'restaurant');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 97 | `return 'https://' . trim($slug, '-') . '.example.com';` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 98 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 99 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

RandomData::fullName()はFIRSTとLASTの候補から1件ずつ選んで結合する。

## ⑤ 最重要部分の再確認

1. 候補一覧を定数に閉じ込める。
2. pickは空配列を拒否。
3. random_intは両端を含む。
4. postalCodeは5桁0埋め文字列。
5. startDateは過去のDateTimeImmutable。
6. safeDomainは例示専用example.comドメイン。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

