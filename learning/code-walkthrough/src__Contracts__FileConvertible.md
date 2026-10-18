# src/Contracts/FileConvertible.php — 全文・逐次解説

## ① ファイル概要

モデルが共通して実装する3つの変換メソッドの約束を宣言する。

**場所**：`src/Contracts/FileConvertible.php`。
**入出力と他ファイルの関係**：interfaceは実際の文字列を生成しない。各モデルがtoString/toHTML/toMarkdownを実装する。

**前提知識**：interfaceは実装のないメソッド署名の集まり, toStringの戻り値はstring, toHTMLとtoMarkdownもstringを返す。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Contracts/FileConvertible.php` の全文で、生成時のSHA-256は `c0cf689d7f29a36bd29ba299b3fa5db9ed508888371fa75586ca05de9aafdd01`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Contracts;

/** A common contract for plain text, HTML and Markdown output. */
interface FileConvertible
{
    public function toString(): string;
    public function toHTML(): string;
    public function toMarkdown(): string;
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
| 4 | `namespace RestaurantMockup\Contracts;` | 名前空間の宣言。クラス名衝突を防ぎ、autoloadのパス対応を決める。 |
| 5 | `(空行)` | 空行。論理的な段落の境界。 |
| 6 | `/** A common contract for plain text, HTML and Markdown output. */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 7 | `interface FileConvertible` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |
| 8 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 9 | `public function toString(): string;` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 10 | `public function toHTML(): string;` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |

### 行 11〜12

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `public function toMarkdown(): string;` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 12 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

Exporterは要素がUserでもRestaurantChainでもtoHTML()を呼び出せる。

## ⑤ 最重要部分の再確認

1. interfaceは実装のないメソッド署名の集まり。
2. toStringの戻り値はstring。
3. toHTMLとtoMarkdownもstringを返す。
4. implementsによってメソッドの存在を保証する。
5. JSON用toArrayは本interfaceとは別の設計。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

