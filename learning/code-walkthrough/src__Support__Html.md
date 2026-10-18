# src/Support/Html.php — 全文・逐次解説

## ① ファイル概要

HTML出力とMarkdown出力の最低限の特殊文字処理を共通化する。

**場所**：`src/Support/Html.php`。
**入出力と他ファイルの関係**：htmlspecialcharsによってテキスト値がHTMLタグとして解釈されるのを防ぐ。

**前提知識**：ENT_QUOTESで引用符もエスケープ, ENT_SUBSTITUTEは不正文字を置換, UTF-8を明示。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Support/Html.php` の全文で、生成時のSHA-256は `08beff54759ae581c8911f5e97a5d79734cf77b2d7867f1276d18a16903151a9`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Support;

final class Html
{
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Escape characters that have structural meaning in Markdown tables. */
    public static function markdown(string $value): string
    {
        return str_replace(["\\", '|', "\r", "\n"], ['\\\\', '\\|', '', ' '], $value);
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
| 6 | `final class Html` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |
| 7 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 8 | `public static function escape(string $value): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 9 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 10 | `return htmlspecialchars($value, ENT_QUOTES \| ENT_SUBSTITUTE, 'UTF-8');` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |

### 行 11〜18

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 12 | `(空行)` | 空行。論理的な段落の境界。 |
| 13 | `/** Escape characters that have structural meaning in Markdown tables. */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 14 | `public static function markdown(string $value): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 15 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 16 | `return str_replace(["\\", '\|', "\r", "\n"], ['\\\\', '\\\|', '', ' '], $value);` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 17 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 18 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

User名が <script> のときtoHTMLには &lt;script&gt; が含まれる。

## ⑤ 最重要部分の再確認

1. ENT_QUOTESで引用符もエスケープ。
2. ENT_SUBSTITUTEは不正文字を置換。
3. UTF-8を明示。
4. Markdownではバックスラッシュとパイプを処理。
5. 改行はテーブル崩れを避けるためスペースに。
6. HTML文脈とMarkdown文脈の処理は異なる。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

