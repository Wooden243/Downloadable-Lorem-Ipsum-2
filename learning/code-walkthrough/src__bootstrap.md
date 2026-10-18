# src/bootstrap.php — 全文・逐次解説

## ① ファイル概要

RestaurantMockup名前空間のクラス名をsrc下のファイルへ対応づけるオートローダー。

**場所**：`src/bootstrap.php`。
**入出力と他ファイルの関係**：別ファイルのクラスを必要になったときだけ require_once する。

**前提知識**：spl_autoload_registerは未定義クラス利用時に呼ばれる, 名前空間プレフィックスの一致判定, ファイルパス区切りへの変換。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/bootstrap.php` の全文で、生成時のSHA-256は `e7b0c1c4634ba86d937e021bca2476ea2c5a8739d9cb44ff855625c70ce648ed`。

```php
<?php
declare(strict_types=1);

spl_autoload_register(static function (string $className): void {
    $prefix = 'RestaurantMockup\\';
    if (!str_starts_with($className, $prefix)) {
        return;
    }
    $relative = substr($className, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
```

## ③ 上から下へ — 逐次読解

**行番号は上記ソースコードの行番号に一致する。** 各行の構文・必要性・変更時の影響を確認する。

### 行 1〜10

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 1 | `<?php` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 2 | `declare(strict_types=1);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 3 | `(空行)` | 空行。論理的な段落の境界。 |
| 4 | `spl_autoload_register(static function (string $className): void {` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 5 | `$prefix = 'RestaurantMockup\\';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 6 | `if (!str_starts_with($className, $prefix)) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 7 | `return;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 8 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 9 | `$relative = substr($className, strlen($prefix));` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 10 | `$file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 11〜14

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `if (is_file($file)) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 12 | `require_once $file;` | 依存ファイルを一度だけ読み込む。未読み込みクラスの使用準備をする。 |
| 13 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 14 | `});` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

## ④ 実行トレース

RestaurantMockup\Support\Exporter → src/Support/Exporter.php を探索し読み込む。

## ⑤ 最重要部分の再確認

1. spl_autoload_registerは未定義クラス利用時に呼ばれる。
2. 名前空間プレフィックスの一致判定。
3. ファイルパス区切りへの変換。
4. require_onceは同じPHPファイルを繰り返し実行しない。
5. Composerがなくても依存解決できる。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

