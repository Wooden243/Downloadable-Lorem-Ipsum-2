# src/Models/User.php — 全文・逐次解説

## ① ファイル概要

名前・メール・IDを保持する基本ユーザーモデル。社員モデルの親クラス。

**場所**：`src/Models/User.php`。
**入出力と他ファイルの関係**：コンストラクタで妥当性を検査し、toString/toHTML/toMarkdownとJSON向けtoArrayを提供。

**前提知識**：privateプロパティのカプセル化, コンストラクタは不正なemailを拒否, Employeeがextendsして基本プロパティを再利用。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `src/Models/User.php` の全文で、生成時のSHA-256は `7f04bd6634bdb9ee5b00064549253f7cd8f29513a0315174292d8ee97f82bf37`。

```php
<?php
declare(strict_types=1);

namespace RestaurantMockup\Models;

use RestaurantMockup\Contracts\FileConvertible;
use RestaurantMockup\Support\Html;
use RestaurantMockup\Support\RandomData;

class User implements FileConvertible
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
    ) {
        if ($id <= 0 || trim($name) === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('A user needs a positive ID, name and valid email.');
        }
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }

    public static function RandomGenerator(): self
    {
        $id = random_int(1000, 999999);
        return new self($id, RandomData::fullName(), "user{$id}@example.com");
    }

    /** @return array{id: int, name: string, email: string} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email];
    }

    public function toString(): string
    {
        return "User ID: {$this->id}; Name: {$this->name}; Email: {$this->email}";
    }

    public function toHTML(): string
    {
        return '<p class="user-line">' . Html::escape($this->toString()) . '</p>';
    }

    public function toMarkdown(): string
    {
        return '- User #' . $this->id . ': ' . Html::markdown($this->name)
            . ' (' . Html::markdown($this->email) . ')';
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
| 6 | `use RestaurantMockup\Contracts\FileConvertible;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `use RestaurantMockup\Support\Html;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 8 | `use RestaurantMockup\Support\RandomData;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 9 | `(空行)` | 空行。論理的な段落の境界。 |
| 10 | `class User implements FileConvertible` | クラス／インターフェース宣言。extendsとimplementsがあれば親子関係や契約を表す。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 12 | `public function __construct(` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 13 | `private int $id,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 14 | `private string $name,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 15 | `private string $email,` | プロパティ公開範囲またはメソッド宣言。データの役割と可視性を確認する。 |
| 16 | `) {` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 17 | `if ($id <= 0 \|\| trim($name) === '' \|\| !filter_var($email, FILTER_VALIDATE_EMAIL)) {` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 18 | `throw new \InvalidArgumentException('A user needs a positive ID, name and valid email.');` | 例外を投げ、呼び出し元のcatchへ失敗を伝える。 |
| 19 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 20 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `(空行)` | 空行。論理的な段落の境界。 |
| 22 | `public function getId(): int { return $this->id; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 23 | `public function getName(): string { return $this->name; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 24 | `public function getEmail(): string { return $this->email; }` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 25 | `(空行)` | 空行。論理的な段落の境界。 |
| 26 | `public static function RandomGenerator(): self` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 27 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 28 | `$id = random_int(1000, 999999);` | 両端を含む整数乱数を生成。テストでは固定値でなく範囲制約を確認する。 |
| 29 | `return new self($id, RandomData::fullName(), "user{$id}@example.com");` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 30 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `(空行)` | 空行。論理的な段落の境界。 |
| 32 | `/** @return array{id: int, name: string, email: string} */` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 33 | `public function toArray(): array` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 34 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 35 | `return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email];` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 36 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 37 | `(空行)` | 空行。論理的な段落の境界。 |
| 38 | `public function toString(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 39 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 40 | `return "User ID: {$this->id}; Name: {$this->name}; Email: {$this->email}";` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 42 | `(空行)` | 空行。論理的な段落の境界。 |
| 43 | `public function toHTML(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 44 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 45 | `return '<p class="user-line">' . Html::escape($this->toString()) . '</p>';` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 46 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 47 | `(空行)` | 空行。論理的な段落の境界。 |
| 48 | `public function toMarkdown(): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 49 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 50 | `return '- User #' . $this->id . ': ' . Html::markdown($this->name)` | Markdown内の特殊文字や改行をエスケープする。 |

### 行 51〜53

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `. ' (' . Html::markdown($this->email) . ')';` | Markdown内の特殊文字や改行をエスケープする。 |
| 52 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 53 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |

## ④ 実行トレース

User(1,"Avery Reed","user1@example.com")をtoArrayするとid/name/emailの連想配列になる。

## ⑤ 最重要部分の再確認

1. privateプロパティのカプセル化。
2. コンストラクタは不正なemailを拒否。
3. Employeeがextendsして基本プロパティを再利用。
4. toArrayがJSONフィールドを定義。
5. HTMLではHtml::escapeを使う。
6. toMarkdownでは特殊文字を変換。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

