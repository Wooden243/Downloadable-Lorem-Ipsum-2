# src/Support/RequestValidator.php — 全文・逐次解説

## ① ファイル概要

サーバー側の全フォーム入力を検証し、文字列を型付きパラメータへ変換する。

**場所**：`src/Support/RequestValidator.php`。
**入出力と他ファイルの関係**：無効値ではInvalidArgumentExceptionを投げ、download.phpがHTTP412へ変換する。

**前提知識**：formatは4種類にホワイトリスト限定, datasetはusers/chains, usersのcountは1〜100。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Support/RequestValidator.php` の全文で、生成時のSHA-256は `e50584f0f40e943bf5ca1f45f017efcb9d99238c64847e3d7e0e4d95ac55d9c0`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

/** No coercion of missing, array, decimal, exponent or malformed POST values. */
final class RequestValidator
{
    private const FORMATS = ['html', 'json', 'txt', 'md'];

    /** @param array<string, mixed> $post
     * @return array<string, int|string>
     */
    public static function validate(array $post): array
    {
        $format = self::choice($post, 'format', self::FORMATS);
        $dataset = array_key_exists('dataset', $post)
            ? self::choice($post, 'dataset', ['users', 'chains']) : 'users';
        if ($dataset === 'users') {
            return [
                'dataset' => 'users',
                'format' => $format,
                'count' => self::integer($post, 'count', 1, 100),
            ];
        }
        $params = [
            'dataset' => 'chains',
            'format' => $format,
            'chain_count' => self::integer($post, 'chain_count', 1, 10),
            'employee_count' => self::integer($post, 'employee_count', 1, 100),
            'location_count' => self::integer($post, 'location_count', 1, 20),
            'salary_min' => self::integer($post, 'salary_min', 0, 1000000),
            'salary_max' => self::integer($post, 'salary_max', 0, 1000000),
            'postal_min' => self::integer($post, 'postal_min', 0, 99999),
            'postal_max' => self::integer($post, 'postal_max', 0, 99999),
        ];
        if ($params['salary_min'] > $params['salary_max']) {
            throw new \InvalidArgumentException('salary_min cannot exceed salary_max.');
        }
        if ($params['postal_min'] > $params['postal_max']) {
            throw new \InvalidArgumentException('postal_min cannot exceed postal_max.');
        }
        return $params;
    }

    /** @param array<string, mixed> $post */
    private static function integer(array $post, string $key, int $min, int $max): int
    {
        $raw = $post[$key] ?? null;
        if (!is_string($raw) && !is_int($raw)) {
            throw new \InvalidArgumentException("Missing or invalid {$key}.");
        }
        $value = (string) $raw;
        if (!preg_match('/\A(?:0|[1-9][0-9]*)\z/D', $value) || strlen($value) > 10) {
            throw new \InvalidArgumentException("{$key} must be a whole number.");
        }
        $number = (int) $value;
        if ($number < $min || $number > $max) {
            throw new \InvalidArgumentException("{$key} must be between {$min} and {$max}.");
        }
        return $number;
    }

    /** @param array<string, mixed> $post
     * @param list<string> $allowed
     */
    private static function choice(array $post, string $key, array $allowed): string
    {
        $value = $post[$key] ?? null;
        if (!is_string($value) || !in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid or missing {$key}; choose " . implode(', ', $allowed) . '.');
        }
        return $value;
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
| 6 | `/** No coercion of missing, array, decimal, exponent or malformed POST values. */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 7 | `final class RequestValidator` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |
| 8 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 9 | `private const FORMATS = ['html', 'json', 'txt', 'md'];` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 10 | `(空行)` | 空行。論理的な段落の境界。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `/** @param array<string, mixed> $post` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 12 | `* @return array<string, int\|string>` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 13 | `*/` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 14 | `public static function validate(array $post): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 15 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 16 | `$format = self::choice($post, 'format', self::FORMATS);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 17 | `$dataset = array_key_exists('dataset', $post)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 18 | `? self::choice($post, 'dataset', ['users', 'chains']) : 'users';` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 19 | `if ($dataset === 'users') {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 20 | `return [` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `'dataset' => 'users',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 22 | `'format' => $format,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 23 | `'count' => self::integer($post, 'count', 1, 100),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 24 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 25 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 26 | `$params = [` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 27 | `'dataset' => 'chains',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 28 | `'format' => $format,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 29 | `'chain_count' => self::integer($post, 'chain_count', 1, 10),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 30 | `'employee_count' => self::integer($post, 'employee_count', 1, 100),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `'location_count' => self::integer($post, 'location_count', 1, 20),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 32 | `'salary_min' => self::integer($post, 'salary_min', 0, 1000000),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 33 | `'salary_max' => self::integer($post, 'salary_max', 0, 1000000),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 34 | `'postal_min' => self::integer($post, 'postal_min', 0, 99999),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 35 | `'postal_max' => self::integer($post, 'postal_max', 0, 99999),` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 36 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 37 | `if ($params['salary_min'] > $params['salary_max']) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 38 | `throw new \InvalidArgumentException('salary_min cannot exceed salary_max.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 39 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 40 | `if ($params['postal_min'] > $params['postal_max']) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `throw new \InvalidArgumentException('postal_min cannot exceed postal_max.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 42 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 43 | `return $params;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 44 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 45 | `(空行)` | 空行。論理的な段落の境界。 |
| 46 | `/** @param array<string, mixed> $post */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 47 | `private static function integer(array $post, string $key, int $min, int $max): int` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 48 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 49 | `$raw = $post[$key] ?? null;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 50 | `if (!is_string($raw) && !is_int($raw)) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `throw new \InvalidArgumentException("Missing or invalid {$key}.");` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 52 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 53 | `$value = (string) $raw;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 54 | `if (!preg_match('/\A(?:0\|[1-9][0-9]*)\z/D', $value) \|\| strlen($value) > 10) {` | 正規表現で整数表記を厳格にチェック。型キャストだけでは不十分。 |
| 55 | `throw new \InvalidArgumentException("{$key} must be a whole number.");` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 56 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 57 | `$number = (int) $value;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 58 | `if ($number < $min \|\| $number > $max) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 59 | `throw new \InvalidArgumentException("{$key} must be between {$min} and {$max}.");` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 60 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `return $number;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 62 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 63 | `(空行)` | 空行。論理的な段落の境界。 |
| 64 | `/** @param array<string, mixed> $post` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 65 | `* @param list<string> $allowed` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 66 | `*/` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 67 | `private static function choice(array $post, string $key, array $allowed): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 68 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 69 | `$value = $post[$key] ?? null;` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 70 | `if (!is_string($value) \|\| !in_array($value, $allowed, true)) {` | 許可された値の集合に含まれるか確認。第3引数trueなら型も厳密に比較。 |

### 行 71〜75

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `throw new \InvalidArgumentException("Invalid or missing {$key}; choose " . implode(', ', $allowed) . '.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 72 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 73 | `return $value;` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 74 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 75 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

count="5"→整数5、count="2.5"→例外、postal_min>postal_max→例外。

## ⑤ 最重要部分の再確認

1. formatは4種類にホワイトリスト限定。
2. datasetはusers/chains。
3. usersのcountは1〜100。
4. チェーン設定値は個別の整数範囲。
5. salary_min<=salary_max。
6. postal_min<=postal_max。
7. 配列値の注入を拒否。
8. 正規表現で小数・指数を拒否。
9. 不正値は生成前に止める。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

