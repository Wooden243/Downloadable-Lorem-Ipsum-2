# src/Support/RandomGenerator.php — 全文・逐次解説

## ① ファイル概要

送信値で指定された件数・範囲を正確に満たすUser/RestaurantChainのインスタンスを構築するファクトリー。

**場所**：`src/Support/RandomGenerator.php`。
**入出力と他ファイルの関係**：Userは指定個数、Chainは指定チェーン数・店舗数・総従業員数を生成。給与ZIP範囲はinclusive。

**前提知識**：users(min,max)は例題の呼び出し形式に対応, count固定ならusers(count,count), Factory自身も上下限検証。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Support/RandomGenerator.php` の全文で、生成時のSHA-256は `be8a1abee4091e69e0cf99bdc2fae9d338604d08c080af9e4ff53a620c079e0f`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

use RestaurantMockup\Models\Company;
use RestaurantMockup\Models\Employee;
use RestaurantMockup\Models\RestaurantChain;
use RestaurantMockup\Models\RestaurantLocation;
use RestaurantMockup\Models\User;

/** Explicit, configurable fixtures for the HTML form and POST exporter. */
final class RandomGenerator
{
    /**
     * Example-compatible: RandomGenerator::users($count, $count).
     * Arguments mean minimum and maximum generated user count (inclusive).
     * @return list<User>
     */
    public static function users(int $minimum, int $maximum): array
    {
        if ($minimum < 1 || $maximum > 100 || $minimum > $maximum) {
            throw new \InvalidArgumentException('User count must be between 1 and 100.');
        }
        $count = random_int($minimum, $maximum);
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $users[] = new User($i, RandomData::fullName(), "user{$i}@example.com");
        }
        return $users;
    }

    /**
     * Creates exactly $chainCount chains, each with exactly $locationCount stores
     * and $employeeCount staff members distributed round-robin across stores.
     * All salaries and ZIP codes fall within the inclusive ranges supplied.
     * @return list<RestaurantChain>
     */
    public static function chains(
        int $chainCount,
        int $employeeCount,
        int $locationCount,
        int $salaryMin,
        int $salaryMax,
        int $postalMin,
        int $postalMax,
    ): array {
        if ($chainCount < 1 || $chainCount > 10 || $employeeCount < 1 || $employeeCount > 100
            || $locationCount < 1 || $locationCount > 20 || $salaryMin < 0
            || $salaryMax > 1000000 || $salaryMin > $salaryMax || $postalMin < 0
            || $postalMax > 99999 || $postalMin > $postalMax) {
            throw new \InvalidArgumentException('Invalid chain generation configuration.');
        }

        $chains = [];
        $nextEmployeeId = 1;
        for ($i = 0; $i < $chainCount; $i++) {
            $company = Company::RandomGenerator();
            $chain = new RestaurantChain(
                $company->getName(), $company->getYearFounded(), $company->getDescription(),
                $company->getWebsite(), $company->getPhone(), $company->getIndustry(),
                $company->getCeo(), $company->isPubliclyTraded(), 1000 + $i,
                RandomData::cuisine(), random_int(0, 1) === 1, 'Independent',
            );
            $locations = [];
            for ($j = 0; $j < $locationCount; $j++) {
                [$city, $state] = RandomData::location();
                $locations[] = new RestaurantLocation(
                    RandomData::restaurantName(), RandomData::streetAddress(), $city, $state,
                    str_pad((string) random_int($postalMin, $postalMax), 5, '0', STR_PAD_LEFT),
                    random_int(0, 9) !== 0,
                );
            }
            for ($j = 0; $j < $employeeCount; $j++) {
                $title = RandomData::jobTitle();
                $id = $nextEmployeeId++;
                $employee = new Employee(
                    $id, RandomData::fullName(), "employee{$id}@example.com",
                    $title, (float) random_int($salaryMin, $salaryMax),
                    RandomData::startDate(), [$title],
                );
                $locations[$j % $locationCount]->addEmployee($employee);
            }
            foreach ($locations as $location) {
                $chain->addLocation($location);
            }
            $chains[] = $chain;
        }
        return $chains;
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
| 6 | `use RestaurantMockup\Models\Company;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `use RestaurantMockup\Models\Employee;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 8 | `use RestaurantMockup\Models\RestaurantChain;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 9 | `use RestaurantMockup\Models\RestaurantLocation;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 10 | `use RestaurantMockup\Models\User;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `(空行)` | 空行。論理的な段落の境界。 |
| 12 | `/** Explicit, configurable fixtures for the HTML form and POST exporter. */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 13 | `final class RandomGenerator` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |
| 14 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 15 | `/**` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 16 | `* Example-compatible: RandomGenerator::users($count, $count).` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 17 | `* Arguments mean minimum and maximum generated user count (inclusive).` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 18 | `* @return list<User>` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 19 | `*/` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 20 | `public static function users(int $minimum, int $maximum): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 22 | `if ($minimum < 1 \|\| $maximum > 100 \|\| $minimum > $maximum) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 23 | `throw new \InvalidArgumentException('User count must be between 1 and 100.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 24 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 25 | `$count = random_int($minimum, $maximum);` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 26 | `$users = [];` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 27 | `for ($i = 1; $i <= $count; $i++) {` | 指定回数だけ繰り返すループ。初期値・条件・増分が件数を決定する。 |
| 28 | `$users[] = new User($i, RandomData::fullName(), "user{$i}@example.com");` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 29 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 30 | `return $users;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 32 | `(空行)` | 空行。論理的な段落の境界。 |
| 33 | `/**` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 34 | `* Creates exactly $chainCount chains, each with exactly $locationCount stores` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 35 | `* and $employeeCount staff members distributed round-robin across stores.` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 36 | `* All salaries and ZIP codes fall within the inclusive ranges supplied.` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 37 | `* @return list<RestaurantChain>` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 38 | `*/` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 39 | `public static function chains(` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 40 | `int $chainCount,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `int $employeeCount,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 42 | `int $locationCount,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 43 | `int $salaryMin,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 44 | `int $salaryMax,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 45 | `int $postalMin,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 46 | `int $postalMax,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 47 | `): array {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 48 | `if ($chainCount < 1 \|\| $chainCount > 10 \|\| $employeeCount < 1 \|\| $employeeCount > 100` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 49 | `\|\| $locationCount < 1 \|\| $locationCount > 20 \|\| $salaryMin < 0` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 50 | `\|\| $salaryMax > 1000000 \|\| $salaryMin > $salaryMax \|\| $postalMin < 0` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `\|\| $postalMax > 99999 \|\| $postalMin > $postalMax) {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 52 | `throw new \InvalidArgumentException('Invalid chain generation configuration.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 53 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 54 | `(空行)` | 空行。論理的な段落の境界。 |
| 55 | `$chains = [];` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 56 | `$nextEmployeeId = 1;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 57 | `for ($i = 0; $i < $chainCount; $i++) {` | 指定回数だけ繰り返すループ。初期値・条件・増分が件数を決定する。 |
| 58 | `$company = Company::RandomGenerator();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 59 | `$chain = new RestaurantChain(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 60 | `$company->getName(), $company->getYearFounded(), $company->getDescription(),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `$company->getWebsite(), $company->getPhone(), $company->getIndustry(),` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 62 | `$company->getCeo(), $company->isPubliclyTraded(), 1000 + $i,` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 63 | `RandomData::cuisine(), random_int(0, 1) === 1, 'Independent',` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 64 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 65 | `$locations = [];` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 66 | `for ($j = 0; $j < $locationCount; $j++) {` | 指定回数だけ繰り返すループ。初期値・条件・増分が件数を決定する。 |
| 67 | `[$city, $state] = RandomData::location();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 68 | `$locations[] = new RestaurantLocation(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 69 | `RandomData::restaurantName(), RandomData::streetAddress(), $city, $state,` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 70 | `str_pad((string) random_int($postalMin, $postalMax), 5, '0', STR_PAD_LEFT),` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |

### 行 71〜80

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `random_int(0, 9) !== 0,` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 72 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 73 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 74 | `for ($j = 0; $j < $employeeCount; $j++) {` | 指定回数だけ繰り返すループ。初期値・条件・増分が件数を決定する。 |
| 75 | `$title = RandomData::jobTitle();` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 76 | `$id = $nextEmployeeId++;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 77 | `$employee = new Employee(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 78 | `$id, RandomData::fullName(), "employee{$id}@example.com",` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 79 | `$title, (float) random_int($salaryMin, $salaryMax),` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 80 | `RandomData::startDate(), [$title],` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |

### 行 81〜90

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 81 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 82 | `$locations[$j % $locationCount]->addEmployee($employee);` | Employeeオブジェクトを該当店舗の社員一覧に追加する。 |
| 83 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 84 | `foreach ($locations as $location) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 85 | `$chain->addLocation($location);` | RestaurantLocationをRestaurantChainの店舗一覧に登録する。 |
| 86 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 87 | `$chains[] = $chain;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 88 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 89 | `return $chains;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 90 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 91〜91

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 91 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

chains(2,7,3,42000,43000,20,50) → 2チェーン×3店舗、各チェーン7社員、年収とZIPは指定範囲内。

## ⑤ 最重要部分の再確認

1. users(min,max)は例題の呼び出し形式に対応。
2. count固定ならusers(count,count)。
3. Factory自身も上下限検証。
4. 店舗生成と社員生成は別ループ。
5. j % locationCountで社員をラウンドロビン配置。
6. nextEmployeeIdはチェーン横断で一意。
7. str_padにより郵便番号が00020等でも保存される。
8. random_intは上下限を含む。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

