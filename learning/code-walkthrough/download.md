# download.php — 全文・逐次解説

## ① ファイル概要

フォームから届くPOST要求だけを受け付けるHTTPエンドポイント。入力検証・モデル生成・エクスポートの各コンポーネントを呼び出してレスポンスを完成する。

**場所**：`download.php`。
**入出力と他ファイルの関係**：GETは405、妥当なPOSTは200、無効値は412、予期しない障害は500。ヘッダーはecho以前に設定する。

**前提知識**：実行の最上流でbootstrapを一度読み込む, HTTPメソッド制限はデータ生成より先, バリデーション成功後だけ生成。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `download.php` の全文で、生成時のSHA-256は `ceacb1212fa49cd5611d8228354ed80c617a9f05ed587c732f0c2828ec7dfa1c`。

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

use RestaurantMockup\Support\Exporter;
use RestaurantMockup\Support\RandomGenerator;
use RestaurantMockup\Support\RequestValidator;

// Download responses must not leak form values or HTML before HTTP headers.
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; frame-ancestors 'none'");

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Method Not Allowed. Submit the form using POST.\n";
    exit;
}

try {
    $parameters = RequestValidator::validate($_POST);
    if ($parameters['dataset'] === 'users') {
        $items = RandomGenerator::users($parameters['count'], $parameters['count']);
    } else {
        $items = RandomGenerator::chains(
            $parameters['chain_count'], $parameters['employee_count'],
            $parameters['location_count'], $parameters['salary_min'],
            $parameters['salary_max'], $parameters['postal_min'], $parameters['postal_max'],
        );
    }
    $export = Exporter::render($items, $parameters['format'], $parameters['dataset']);
    http_response_code(200);
    header('Content-Type: ' . $export['contentType']);
    header('Content-Disposition: ' . $export['disposition']);
    echo $export['body'];
} catch (\InvalidArgumentException $exception) {
    http_response_code(412);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Invalid request: ' . $exception->getMessage() . "\n";
} catch (\Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Internal server error.\n";
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
| 4 | `require_once __DIR__ . '/src/bootstrap.php';` | 依存ファイルを一度だけ読み込む。未読み込みクラスの使用準備をする。 |
| 5 | `(空行)` | 空行。論理的な段落の境界。 |
| 6 | `use RestaurantMockup\Support\Exporter;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `use RestaurantMockup\Support\RandomGenerator;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 8 | `use RestaurantMockup\Support\RequestValidator;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 9 | `(空行)` | 空行。論理的な段落の境界。 |
| 10 | `// Download responses must not leak form values or HTML before HTTP headers.` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `header('X-Content-Type-Options: nosniff');` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 12 | `header('Cache-Control: no-store');` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 13 | `header('X-Frame-Options: DENY');` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 14 | `header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; frame-ance…` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 15 | `(空行)` | 空行。論理的な段落の境界。 |
| 16 | `if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 17 | `header('Allow: POST');` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 18 | `http_response_code(405);` | HTTPステータスコードを設定。成功と入力エラーを機械的に区別する。 |
| 19 | `header('Content-Type: text/plain; charset=UTF-8');` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 20 | `echo "Method Not Allowed. Submit the form using POST.\n";` | 文字列をHTTPレスポンス本文（またはCLI標準出力）へ書き出す。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `exit;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 22 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 23 | `(空行)` | 空行。論理的な段落の境界。 |
| 24 | `try {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 25 | `$parameters = RequestValidator::validate($_POST);` | POSTリクエストで送られたキー・値の連想配列。信頼せず検証してから使う。 |
| 26 | `if ($parameters['dataset'] === 'users') {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 27 | `$items = RandomGenerator::users($parameters['count'], $parameters['count']);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 28 | `} else {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 29 | `$items = RandomGenerator::chains(` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 30 | `$parameters['chain_count'], $parameters['employee_count'],` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `$parameters['location_count'], $parameters['salary_min'],` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 32 | `$parameters['salary_max'], $parameters['postal_min'], $parameters['postal_max'],` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 33 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 34 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 35 | `$export = Exporter::render($items, $parameters['format'], $parameters['dataset']);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 36 | `http_response_code(200);` | HTTPステータスコードを設定。成功と入力エラーを機械的に区別する。 |
| 37 | `header('Content-Type: ' . $export['contentType']);` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 38 | `header('Content-Disposition: ' . $export['disposition']);` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 39 | `echo $export['body'];` | 文字列をHTTPレスポンス本文（またはCLI標準出力）へ書き出す。 |
| 40 | `} catch (\InvalidArgumentException $exception) {` | 例外処理分岐。想定された入力エラーと予期しない障害を区別する。 |

### 行 41〜49

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `http_response_code(412);` | HTTPステータスコードを設定。成功と入力エラーを機械的に区別する。 |
| 42 | `header('Content-Type: text/plain; charset=UTF-8');` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 43 | `echo 'Invalid request: ' . $exception->getMessage() . "\n";` | 文字列をHTTPレスポンス本文（またはCLI標準出力）へ書き出す。 |
| 44 | `} catch (\Throwable $exception) {` | 例外処理分岐。想定された入力エラーと予期しない障害を区別する。 |
| 45 | `error_log($exception->getMessage());` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 46 | `http_response_code(500);` | HTTPステータスコードを設定。成功と入力エラーを機械的に区別する。 |
| 47 | `header('Content-Type: text/plain; charset=UTF-8');` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |
| 48 | `echo "Internal server error.\n";` | 文字列をHTTPレスポンス本文（またはCLI標準出力）へ書き出す。 |
| 49 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

count=3,format=json → validate() → users(3,3) → render(...,json,users) → application/jsonとattachmentを送る。count=0なら例外を受けて412。

## ⑤ 最重要部分の再確認

1. 実行の最上流でbootstrapを一度読み込む。
2. HTTPメソッド制限はデータ生成より先。
3. バリデーション成功後だけ生成。
4. UserとChainはdataset値によって分岐。
5. レスポンスのContent-TypeとDispositionはExporterが決定。
6. 不正入力を412に写像。
7. 500では例外の生文言をクライアントへ出さない。
8. header()は本文出力前。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

