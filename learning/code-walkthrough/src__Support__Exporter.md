# src/Support/Exporter.php — 全文・逐次解説

## ① ファイル概要

モデル配列をHTML/JSON/TXT/Markdownに変換し、MIMEタイプ・ダウンロードヘッダーも返す。

**場所**：`src/Support/Exporter.php`。
**入出力と他ファイルの関係**：変換処理は純粋な関数として使われ、header()そのものはdownload.phpが実行する。

**前提知識**：jsonでは各モデルのtoArrayから構造を維持, JSON_THROW_ON_ERRORで誤変換を検知, textとMarkdownはオブジェクトごとのメソッドを呼ぶ。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Support/Exporter.php` の全文で、生成時のSHA-256は `57fe20a4a06b7ba637452dfcf41c20abce4441b71ed34d846a3c47472e4fe551`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

use RestaurantMockup\Models\RestaurantChain;
use RestaurantMockup\Models\User;
use RestaurantMockup\Contracts\FileConvertible;

final class Exporter
{
    /** @param list<FileConvertible> $items
     * @return array{body: string, contentType: string, disposition: string}
     */
    public static function render(array $items, string $format, string $dataset): array
    {
        if (!in_array($format, ['html', 'json', 'txt', 'md'], true)
            || !in_array($dataset, ['users', 'chains'], true)) {
            throw new \InvalidArgumentException('Unsupported format or dataset.');
        }
        $title = $dataset === 'users' ? 'Generated Users' : 'Restaurant Chain Mockup';
        if ($format === 'json') {
            $arrays = array_map(
                static function (FileConvertible $item): array {
                    if (!$item instanceof User && !$item instanceof RestaurantChain) {
                        throw new \InvalidArgumentException('Cannot serialize unknown fixture type.');
                    }
                    return $item->toArray();
                },
                $items,
            );
            $body = json_encode($arrays, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
            $mime = 'application/json';
        } elseif ($format === 'txt') {
            $body = implode("\n\n", array_map(
                static fn(FileConvertible $item): string => $item->toString(), $items,
            )) . "\n";
            $mime = 'text/plain';
        } elseif ($format === 'md') {
            $body = '# ' . $title . "\n\n" . implode("\n\n", array_map(
                static fn(FileConvertible $item): string => $item->toMarkdown(), $items,
            )) . "\n";
            $mime = 'text/markdown';
        } else {
            $body = self::htmlDocument($items, $title);
            $mime = 'text/html';
        }
        $filename = $dataset === 'users' ? 'users' : 'restaurant-chains';
        return [
            'body' => $body,
            'contentType' => $mime . '; charset=UTF-8',
            'disposition' => $format === 'html' ? 'inline' : 'attachment; filename="' . $filename . '.' . $format . '"',
        ];
    }

    /** @param list<FileConvertible> $items */
    private static function htmlDocument(array $items, string $title): string
    {
        $sections = implode("\n", array_map(
            static fn(FileConvertible $item): string => $item->toHTML(), $items,
        ));
        $safeTitle = Html::escape($title);
        return '<!doctype html><html lang="en"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . $safeTitle . '</title><style>'
            . 'body{font-family:system-ui,Arial,sans-serif;background:#f3f6fa;color:#1f2937;margin:0}'
            . 'main{max-width:980px;margin:32px auto;background:white;padding:28px;border-radius:14px}'
            . '.chain{margin:2rem 0}.chain-card,.location{border:1px solid #d4dce8;border-radius:6px;padding:12px}'
            . '.location{margin:10px 0}.employee-row{padding:6px}.user-line{border-bottom:1px solid #ddd}'
            . 'summary{cursor:pointer;color:#0759b6}.company-facts div{display:flex;gap:12px;padding:5px}'
            . 'dt{font-weight:bold}dd{margin:0}a{color:#0759b6}'
            . '</style></head><body><main><p><a href="/generate.php">&larr; Back to form</a></p>'
            . '<h1>' . $safeTitle . '</h1>' . $sections . '</main></body></html>';
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
| 6 | `use RestaurantMockup\Models\RestaurantChain;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `use RestaurantMockup\Models\User;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 8 | `use RestaurantMockup\Contracts\FileConvertible;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 9 | `(空行)` | 空行。論理的な段落の境界。 |
| 10 | `final class Exporter` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 12 | `/** @param list<FileConvertible> $items` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 13 | `* @return array{body: string, contentType: string, disposition: string}` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 14 | `*/` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 15 | `public static function render(array $items, string $format, string $dataset): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 16 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 17 | `if (!in_array($format, ['html', 'json', 'txt', 'md'], true)` | 許可された値の集合に含まれるか確認。第3引数trueなら型も厳密に比較。 |
| 18 | `\|\| !in_array($dataset, ['users', 'chains'], true)) {` | 許可された値の集合に含まれるか確認。第3引数trueなら型も厳密に比較。 |
| 19 | `throw new \InvalidArgumentException('Unsupported format or dataset.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 20 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `$title = $dataset === 'users' ? 'Generated Users' : 'Restaurant Chain Mockup';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 22 | `if ($format === 'json') {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 23 | `$arrays = array_map(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 24 | `static function (FileConvertible $item): array {` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 25 | `if (!$item instanceof User && !$item instanceof RestaurantChain) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 26 | `throw new \InvalidArgumentException('Cannot serialize unknown fixture type.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 27 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 28 | `return $item->toArray();` | モデル属性をJSONへ変換しやすいPHP配列へ組み立てる。 |
| 29 | `},` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 30 | `$items,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `);` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 32 | `$body = json_encode($arrays, JSON_PRETTY_PRINT \| JSON_UNESCAPED_SLASHES` | PHPデータ構造とJSONテキストを変換。型とエラー処理が重要。 |
| 33 | `\| JSON_UNESCAPED_UNICODE \| JSON_THROW_ON_ERROR) . "\n";` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 34 | `$mime = 'application/json';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 35 | `} elseif ($format === 'txt') {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 36 | `$body = implode("\n\n", array_map(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 37 | `static fn(FileConvertible $item): string => $item->toString(), $items,` | モデルをテキスト表現に変換する。 |
| 38 | `)) . "\n";` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 39 | `$mime = 'text/plain';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 40 | `} elseif ($format === 'md') {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `$body = '# ' . $title . "\n\n" . implode("\n\n", array_map(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 42 | `static fn(FileConvertible $item): string => $item->toMarkdown(), $items,` | モデルをMarkdownの見出し・リストなどへ整形する。 |
| 43 | `)) . "\n";` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 44 | `$mime = 'text/markdown';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 45 | `} else {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 46 | `$body = self::htmlDocument($items, $title);` | クラスの静的関数や親クラスの機能を利用する呼び出し。 |
| 47 | `$mime = 'text/html';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 48 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 49 | `$filename = $dataset === 'users' ? 'users' : 'restaurant-chains';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 50 | `return [` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `'body' => $body,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 52 | `'contentType' => $mime . '; charset=UTF-8',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 53 | `'disposition' => $format === 'html' ? 'inline' : 'attachment; filename="' . $filename . '.' . $format . '"',` | フォームフィールド名を定義。サーバー側のPOST配列のキーに対応する。 |
| 54 | `];` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 55 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 56 | `(空行)` | 空行。論理的な段落の境界。 |
| 57 | `/** @param list<FileConvertible> $items */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 58 | `private static function htmlDocument(array $items, string $title): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 59 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 60 | `$sections = implode("\n", array_map(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `static fn(FileConvertible $item): string => $item->toHTML(), $items,` | モデルからHTMLマークアップを生成するメソッド。出力値はescape済みであるべき。 |
| 62 | `));` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 63 | `$safeTitle = Html::escape($title);` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 64 | `return '<!doctype html><html lang="en"><head><meta charset="UTF-8">'` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 65 | `. '<meta name="viewport" content="width=device-width,initial-scale=1">'` | フォームフィールド名を定義。サーバー側のPOST配列のキーに対応する。 |
| 66 | `. '<title>' . $safeTitle . '</title><style>'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 67 | `. 'body{font-family:system-ui,Arial,sans-serif;background:#f3f6fa;color:#1f2937;margin:0}'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 68 | `. 'main{max-width:980px;margin:32px auto;background:white;padding:28px;border-radius:14px}'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 69 | `. '.chain{margin:2rem 0}.chain-card,.location{border:1px solid #d4dce8;border-radius:6px;padding:12px}'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 70 | `. '.location{margin:10px 0}.employee-row{padding:6px}.user-line{border-bottom:1px solid #ddd}'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 71〜76

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `. 'summary{cursor:pointer;color:#0759b6}.company-facts div{display:flex;gap:12px;padding:5px}'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 72 | `. 'dt{font-weight:bold}dd{margin:0}a{color:#0759b6}'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 73 | `. '</style></head><body><main><p><a href="/generate.php">&larr; Back to form</a></p>'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 74 | `. '<h1>' . $safeTitle . '</h1>' . $sections . '</main></body></html>';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 75 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 76 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

json→toArrayをjson_encode; md→toMarkdownをjoin; html→DOCTYPE付きHTMLとinline; txt→toString。

## ⑤ 最重要部分の再確認

1. jsonでは各モデルのtoArrayから構造を維持。
2. JSON_THROW_ON_ERRORで誤変換を検知。
3. textとMarkdownはオブジェクトごとのメソッドを呼ぶ。
4. htmlは完全な文書を生成。
5. HTMLだけdisposition inline。
6. 他は固定名のattachment。
7. 返すのはbody/contentType/dispositionの連想配列。
8. 文書のタイトルはHTMLエスケープ。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

