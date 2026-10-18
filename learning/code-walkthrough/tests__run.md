# tests/run.php — 全文・逐次解説

## ① ファイル概要

ブラウザなしでモデル、検証、変換、エスケープの条件を確認するPHPテストハーネス。

**場所**：`tests/run.php`。
**入出力と他ファイルの関係**：check()が成功/失敗件数を集計し、1件でも失敗したらexit(1)でCI等に失敗を伝える。

**前提知識**：個々の文字列値でなく不変条件をテスト, rejects()で例外が発生することを保証, 出力形式4つ×データセット2つの組合せ。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `tests/run.php` の全文で、生成時のSHA-256は `e93109da6ce60920825085f63198effbe0b64f48980918a3f1eaf0b5cebe87bf`。

```php
<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Models\Employee;
use RestaurantMockup\Models\RestaurantChain;
use RestaurantMockup\Models\User;
use RestaurantMockup\Support\Exporter;
use RestaurantMockup\Support\RandomGenerator;
use RestaurantMockup\Support\RequestValidator;

$passed = 0;
$failed = 0;

function check(bool $condition, string $description): void
{
    global $passed, $failed;
    if (!$condition) {
        $failed++;
        echo "FAIL {$description}\n";
        return;
    }
    $passed++;
    echo "PASS {$description}\n";
}

function rejects(callable $action): bool
{
    try {
        $action();
    } catch (InvalidArgumentException $exception) {
        return true;
    }
    return false;
}

$users = RandomGenerator::users(5, 5);
check(count($users) === 5, 'users(): exact requested count');
check($users[0] instanceof User && $users[0] instanceof FileConvertible, 'User implements FileConvertible');
check(count(array_unique(array_map(static fn(User $u): int => $u->getId(), $users))) === 5, 'User IDs are unique');
check($users[0]->toArray()['id'] === 1, 'User toArray exposes ID');
check(str_contains($users[0]->toString(), 'Email:'), 'User toString serializes email');
check(str_contains($users[0]->toHTML(), 'user-line'), 'User toHTML renders a paragraph');
check(str_contains($users[0]->toMarkdown(), 'User #'), 'User toMarkdown renders a record');
check(rejects(static fn() => RandomGenerator::users(0, 5)), 'Generator rejects min user count 0');
check(rejects(static fn() => RandomGenerator::users(10, 101)), 'Generator rejects max user count >100');
check(rejects(static fn() => RandomGenerator::users(5, 4)), 'Generator rejects inverted user count');

$settings = [
    'dataset' => 'chains', 'format' => 'json', 'chain_count' => '2',
    'employee_count' => '7', 'location_count' => '3',
    'salary_min' => '42000', 'salary_max' => '43000',
    'postal_min' => '20', 'postal_max' => '50',
];
$params = RequestValidator::validate($settings);
check($params['chain_count'] === 2 && $params['employee_count'] === 7, 'Chain count fields validated');
check($params['salary_min'] === 42000 && $params['postal_max'] === 50, 'Range fields validated');
$chains = RandomGenerator::chains(2, 7, 3, 42000, 43000, 20, 50);
check(count($chains) === 2, 'Generator returns exactly 2 chains');
check($chains[0] instanceof RestaurantChain && $chains[0] instanceof FileConvertible, 'RestaurantChain preserves inheritance');
check(count($chains[0]->getRestaurantLocations()) === 3, 'Each chain has exactly 3 locations');
$allIds = [];
$allEmployeesCorrect = true;
$allPostalCorrect = true;
$allSalariesCorrect = true;
foreach ($chains as $chain) {
    $employeeNumber = 0;
    foreach ($chain->getRestaurantLocations() as $location) {
        $postcode = (int) $location->getPostalCode();
        $allPostalCorrect = $allPostalCorrect && strlen($location->getPostalCode()) === 5 && $postcode >= 20 && $postcode <= 50;
        foreach ($location->getEmployees() as $employee) {
            $allSalariesCorrect = $allSalariesCorrect && $employee instanceof Employee
                && $employee->getSalary() >= 42000 && $employee->getSalary() <= 43000;
            $employeeNumber++;
            $allIds[] = $employee->getId();
        }
    }
    $allEmployeesCorrect = $allEmployeesCorrect && $employeeNumber === 7;
}
check($allEmployeesCorrect, 'Exactly 7 employees total per chain');
check($allPostalCorrect, 'Postal codes formatted with 5 digits inside inclusive range');
check($allSalariesCorrect, 'Salaries remain inside requested inclusive range');
check(count(array_unique($allIds)) === count($allIds), 'Employee IDs unique across chains');
check($chains[0]->toArray()['numberOfLocations'] === 3, 'Chain JSON has location count');
check(count($chains[0]->toArray()['restaurantLocations'][0]['employees']) >= 2, 'Chain JSON retains nested employee data');
check(str_contains($chains[0]->toHTML(), '<details'), 'Chain HTML has expandable location');
check(str_contains($chains[0]->toMarkdown(), 'Restaurant Chain'), 'Chain Markdown includes heading');

foreach (['users' => $users, 'chains' => $chains] as $dataset => $items) {
    foreach (['html', 'json', 'txt', 'md'] as $format) {
        $result = Exporter::render($items, $format, $dataset);
        check(str_contains($result['contentType'], 'charset=UTF-8'), "{$dataset}/{$format} content type UTF-8");
        check(strlen($result['body']) > 30, "{$dataset}/{$format} nonempty content");
        check(str_starts_with($result['disposition'], $format === 'html' ? 'inline' : 'attachment'), "{$dataset}/{$format} download disposition");
        if ($format === 'json') {
            $data = json_decode($result['body'], true, 512, JSON_THROW_ON_ERROR);
            check(count($data) === count($items), "{$dataset}/json valid JSON and count");
        }
    }
}

check(RequestValidator::validate(['count' => '5', 'format' => 'md'])['dataset'] === 'users', 'Dataset defaults to users for sample-compatible POST');
check(rejects(static fn() => RequestValidator::validate(['count' => '5'])), 'Missing format rejected');
check(rejects(static fn() => RequestValidator::validate(['format' => 'json'])), 'Missing count rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '0', 'format' => 'json'])), 'Count below 1 rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '101', 'format' => 'json'])), 'Count above 100 rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '2.5', 'format' => 'json'])), 'Decimal count rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '1e2', 'format' => 'json'])), 'Exponent count rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => ['5'], 'format' => 'json'])), 'Array injection count rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '5', 'format' => ['json']])), 'Array injection format rejected');
check(rejects(static fn() => RequestValidator::validate(['count' => '5', 'format' => 'xml'])), 'Unsupported output type rejected');
check(rejects(static fn() => RequestValidator::validate(['dataset' => 'wrong', 'format' => 'json', 'count' => '5'])), 'Unknown dataset rejected');
$badSalary = $settings;
$badSalary['salary_min'] = '100000';
check(rejects(static fn() => RequestValidator::validate($badSalary)), 'Inverted salary range rejected');
$badPostal = $settings;
$badPostal['postal_min'] = '99';
check(rejects(static fn() => RequestValidator::validate($badPostal)), 'Inverted postal code range rejected');
$badPostal['postal_min'] = '0';
$badPostal['postal_max'] = '100000';
check(rejects(static fn() => RequestValidator::validate($badPostal)), 'Postal code >99999 rejected');
$badSettings = $settings;
$badSettings['location_count'] = '21';
check(rejects(static fn() => RequestValidator::validate($badSettings)), 'Location count >20 rejected');
check(rejects(static fn() => RandomGenerator::chains(1, 3, 2, 0, 2000000, 0, 99999)), 'Direct generator rejects out-of-range salaries');
check(rejects(static fn() => Exporter::render($users, 'xml', 'users')), 'Exporter refuses unrecognized format');

// HTML escaping should prevent a model containing markup from injecting elements.
$dangerous = new User(100, '<script>alert(1)</script>', 'safe@example.com');
$html = Exporter::render([$dangerous], 'html', 'users')['body'];
check(!str_contains($html, '<script>'), 'HTML export escapes a script tag');
check(str_contains($html, '&lt;script&gt;'), 'HTML export shows escaped script as text');

// Ensure zero-padded codes are not converted to numbers in JSON.
$zeroZipChain = RandomGenerator::chains(1, 1, 1, 10, 10, 5, 5)[0];
$json = Exporter::render([$zeroZipChain], 'json', 'chains')['body'];
check(str_contains($json, '"postalCode": "00005"'), 'JSON preserves zero-padded ZIP string');
check($zeroZipChain->getRestaurantLocations()[0]->getEmployees()[0]->getSalary() === 10.0, 'Inclusive salary bounds can be equal');

printf("\nSUMMARY: %d passed, %d failed\n", $passed, $failed);
exit($failed > 0 ? 1 : 0);
```

## ③ 上から下へ — 逐次読解

**行番号は上記ソースコードの行番号に一致する。** 各行の構文・必要性・変更時の影響を確認する。

### 行 1〜10

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 1 | `<?php` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 2 | `declare(strict_types=1);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 3 | `(空行)` | 空行。論理的な段落の境界。 |
| 4 | `require_once dirname(__DIR__) . '/src/bootstrap.php';` | 依存ファイルを一度だけ読み込む。未読み込みクラスの使用準備をする。 |
| 5 | `(空行)` | 空行。論理的な段落の境界。 |
| 6 | `use RestaurantMockup\Contracts\FileConvertible;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `use RestaurantMockup\Models\Employee;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 8 | `use RestaurantMockup\Models\RestaurantChain;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 9 | `use RestaurantMockup\Models\User;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 10 | `use RestaurantMockup\Support\Exporter;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `use RestaurantMockup\Support\RandomGenerator;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 12 | `use RestaurantMockup\Support\RequestValidator;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 13 | `(空行)` | 空行。論理的な段落の境界。 |
| 14 | `$passed = 0;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 15 | `$failed = 0;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 16 | `(空行)` | 空行。論理的な段落の境界。 |
| 17 | `function check(bool $condition, string $description): void` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 18 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 19 | `global $passed, $failed;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 20 | `if (!$condition) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `$failed++;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 22 | `echo "FAIL {$description}\n";` | 文字列をHTTPレスポンス本文（またはCLI標準出力）へ書き出す。 |
| 23 | `return;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 24 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 25 | `$passed++;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 26 | `echo "PASS {$description}\n";` | 文字列をHTTPレスポンス本文（またはCLI標準出力）へ書き出す。 |
| 27 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 28 | `(空行)` | 空行。論理的な段落の境界。 |
| 29 | `function rejects(callable $action): bool` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 30 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `try {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 32 | `$action();` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 33 | `} catch (InvalidArgumentException $exception) {` | 例外処理分岐。想定された入力エラーと予期しない障害を区別する。 |
| 34 | `return true;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 35 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 36 | `return false;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 37 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 38 | `(空行)` | 空行。論理的な段落の境界。 |
| 39 | `$users = RandomGenerator::users(5, 5);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 40 | `check(count($users) === 5, 'users(): exact requested count');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `check($users[0] instanceof User && $users[0] instanceof FileConvertible, 'User implements FileConvertible');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 42 | `check(count(array_unique(array_map(static fn(User $u): int => $u->getId(), $users))) === 5, 'User IDs are u…` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 43 | `check($users[0]->toArray()['id'] === 1, 'User toArray exposes ID');` | モデル属性をJSONへ変換しやすいPHP配列へ組み立てる。 |
| 44 | `check(str_contains($users[0]->toString(), 'Email:'), 'User toString serializes email');` | モデルをテキスト表現に変換する。 |
| 45 | `check(str_contains($users[0]->toHTML(), 'user-line'), 'User toHTML renders a paragraph');` | モデルからHTMLマークアップを生成するメソッド。出力値はescape済みであるべき。 |
| 46 | `check(str_contains($users[0]->toMarkdown(), 'User #'), 'User toMarkdown renders a record');` | モデルをMarkdownの見出し・リストなどへ整形する。 |
| 47 | `check(rejects(static fn() => RandomGenerator::users(0, 5)), 'Generator rejects min user count 0');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 48 | `check(rejects(static fn() => RandomGenerator::users(10, 101)), 'Generator rejects max user count >100');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 49 | `check(rejects(static fn() => RandomGenerator::users(5, 4)), 'Generator rejects inverted user count');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 50 | `(空行)` | 空行。論理的な段落の境界。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `$settings = [` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 52 | `'dataset' => 'chains', 'format' => 'json', 'chain_count' => '2',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 53 | `'employee_count' => '7', 'location_count' => '3',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 54 | `'salary_min' => '42000', 'salary_max' => '43000',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 55 | `'postal_min' => '20', 'postal_max' => '50',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 56 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 57 | `$params = RequestValidator::validate($settings);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 58 | `check($params['chain_count'] === 2 && $params['employee_count'] === 7, 'Chain count fields validated');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 59 | `check($params['salary_min'] === 42000 && $params['postal_max'] === 50, 'Range fields validated');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 60 | `$chains = RandomGenerator::chains(2, 7, 3, 42000, 43000, 20, 50);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `check(count($chains) === 2, 'Generator returns exactly 2 chains');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 62 | `check($chains[0] instanceof RestaurantChain && $chains[0] instanceof FileConvertible, 'RestaurantChain pres…` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 63 | `check(count($chains[0]->getRestaurantLocations()) === 3, 'Each chain has exactly 3 locations');` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 64 | `$allIds = [];` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 65 | `$allEmployeesCorrect = true;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 66 | `$allPostalCorrect = true;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 67 | `$allSalariesCorrect = true;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 68 | `foreach ($chains as $chain) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 69 | `$employeeNumber = 0;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 70 | `foreach ($chain->getRestaurantLocations() as $location) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |

### 行 71〜80

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `$postcode = (int) $location->getPostalCode();` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 72 | `$allPostalCorrect = $allPostalCorrect && strlen($location->getPostalCode()) === 5 && $postcode >= 20 && $po…` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 73 | `foreach ($location->getEmployees() as $employee) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 74 | `$allSalariesCorrect = $allSalariesCorrect && $employee instanceof Employee` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 75 | `&& $employee->getSalary() >= 42000 && $employee->getSalary() <= 43000;` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 76 | `$employeeNumber++;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 77 | `$allIds[] = $employee->getId();` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 78 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 79 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 80 | `$allEmployeesCorrect = $allEmployeesCorrect && $employeeNumber === 7;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 81〜90

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 81 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 82 | `check($allEmployeesCorrect, 'Exactly 7 employees total per chain');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 83 | `check($allPostalCorrect, 'Postal codes formatted with 5 digits inside inclusive range');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 84 | `check($allSalariesCorrect, 'Salaries remain inside requested inclusive range');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 85 | `check(count(array_unique($allIds)) === count($allIds), 'Employee IDs unique across chains');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 86 | `check($chains[0]->toArray()['numberOfLocations'] === 3, 'Chain JSON has location count');` | モデル属性をJSONへ変換しやすいPHP配列へ組み立てる。 |
| 87 | `check(count($chains[0]->toArray()['restaurantLocations'][0]['employees']) >= 2, 'Chain JSON retains nested …` | モデル属性をJSONへ変換しやすいPHP配列へ組み立てる。 |
| 88 | `check(str_contains($chains[0]->toHTML(), '<details'), 'Chain HTML has expandable location');` | モデルからHTMLマークアップを生成するメソッド。出力値はescape済みであるべき。 |
| 89 | `check(str_contains($chains[0]->toMarkdown(), 'Restaurant Chain'), 'Chain Markdown includes heading');` | モデルをMarkdownの見出し・リストなどへ整形する。 |
| 90 | `(空行)` | 空行。論理的な段落の境界。 |

### 行 91〜100

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 91 | `foreach (['users' => $users, 'chains' => $chains] as $dataset => $items) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 92 | `foreach (['html', 'json', 'txt', 'md'] as $format) {` | 配列の各要素に対して処理する。現在要素の型と集計方法を確認。 |
| 93 | `$result = Exporter::render($items, $format, $dataset);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 94 | `check(str_contains($result['contentType'], 'charset=UTF-8'), "{$dataset}/{$format} content type UTF-8");` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 95 | `check(strlen($result['body']) > 30, "{$dataset}/{$format} nonempty content");` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 96 | `check(str_starts_with($result['disposition'], $format === 'html' ? 'inline' : 'attachment'), "{$dataset}/{$…` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 97 | `if ($format === 'json') {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 98 | `$data = json_decode($result['body'], true, 512, JSON_THROW_ON_ERROR);` | PHPデータ構造とJSONテキストを変換。型とエラー処理が重要。 |
| 99 | `check(count($data) === count($items), "{$dataset}/json valid JSON and count");` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 100 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 101〜110

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 101 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 102 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 103 | `(空行)` | 空行。論理的な段落の境界。 |
| 104 | `check(RequestValidator::validate(['count' => '5', 'format' => 'md'])['dataset'] === 'users', 'Dataset defau…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 105 | `check(rejects(static fn() => RequestValidator::validate(['count' => '5'])), 'Missing format rejected');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 106 | `check(rejects(static fn() => RequestValidator::validate(['format' => 'json'])), 'Missing count rejected');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 107 | `check(rejects(static fn() => RequestValidator::validate(['count' => '0', 'format' => 'json'])), 'Count belo…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 108 | `check(rejects(static fn() => RequestValidator::validate(['count' => '101', 'format' => 'json'])), 'Count ab…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 109 | `check(rejects(static fn() => RequestValidator::validate(['count' => '2.5', 'format' => 'json'])), 'Decimal …` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 110 | `check(rejects(static fn() => RequestValidator::validate(['count' => '1e2', 'format' => 'json'])), 'Exponent…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |

### 行 111〜120

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 111 | `check(rejects(static fn() => RequestValidator::validate(['count' => ['5'], 'format' => 'json'])), 'Array in…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 112 | `check(rejects(static fn() => RequestValidator::validate(['count' => '5', 'format' => ['json']])), 'Array in…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 113 | `check(rejects(static fn() => RequestValidator::validate(['count' => '5', 'format' => 'xml'])), 'Unsupported…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 114 | `check(rejects(static fn() => RequestValidator::validate(['dataset' => 'wrong', 'format' => 'json', 'count' …` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 115 | `$badSalary = $settings;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 116 | `$badSalary['salary_min'] = '100000';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 117 | `check(rejects(static fn() => RequestValidator::validate($badSalary)), 'Inverted salary range rejected');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 118 | `$badPostal = $settings;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 119 | `$badPostal['postal_min'] = '99';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 120 | `check(rejects(static fn() => RequestValidator::validate($badPostal)), 'Inverted postal code range rejected');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |

### 行 121〜130

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 121 | `$badPostal['postal_min'] = '0';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 122 | `$badPostal['postal_max'] = '100000';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 123 | `check(rejects(static fn() => RequestValidator::validate($badPostal)), 'Postal code >99999 rejected');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 124 | `$badSettings = $settings;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 125 | `$badSettings['location_count'] = '21';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 126 | `check(rejects(static fn() => RequestValidator::validate($badSettings)), 'Location count >20 rejected');` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 127 | `check(rejects(static fn() => RandomGenerator::chains(1, 3, 2, 0, 2000000, 0, 99999)), 'Direct generator rej…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 128 | `check(rejects(static fn() => Exporter::render($users, 'xml', 'users')), 'Exporter refuses unrecognized form…` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 129 | `(空行)` | 空行。論理的な段落の境界。 |
| 130 | `// HTML escaping should prevent a model containing markup from injecting elements.` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |

### 行 131〜140

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 131 | `$dangerous = new User(100, '<script>alert(1)</script>', 'safe@example.com');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 132 | `$html = Exporter::render([$dangerous], 'html', 'users')['body'];` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 133 | `check(!str_contains($html, '<script>'), 'HTML export escapes a script tag');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 134 | `check(str_contains($html, '&lt;script&gt;'), 'HTML export shows escaped script as text');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 135 | `(空行)` | 空行。論理的な段落の境界。 |
| 136 | `// Ensure zero-padded codes are not converted to numbers in JSON.` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 137 | `$zeroZipChain = RandomGenerator::chains(1, 1, 1, 10, 10, 5, 5)[0];` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 138 | `$json = Exporter::render([$zeroZipChain], 'json', 'chains')['body'];` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 139 | `check(str_contains($json, '"postalCode": "00005"'), 'JSON preserves zero-padded ZIP string');` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 140 | `check($zeroZipChain->getRestaurantLocations()[0]->getEmployees()[0]->getSalary() === 10.0, 'Inclusive salar…` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 141〜143

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 141 | `(空行)` | 空行。論理的な段落の境界。 |
| 142 | `printf("\nSUMMARY: %d passed, %d failed\n", $passed, $failed);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 143 | `exit($failed > 0 ? 1 : 0);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

## ④ 実行トレース

User人数5、Chain 2×店舗3×社員7、郵便番号ゼロ埋め、危険なHTML入力などをチェック。

## ⑤ 最重要部分の再確認

1. 個々の文字列値でなく不変条件をテスト。
2. rejects()で例外が発生することを保証。
3. 出力形式4つ×データセット2つの組合せ。
4. JSONはjson_decodeで妥当性確認。
5. 不適切入力は成功として通らないこと。
6. 最後のexit statusで自動化可能。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

