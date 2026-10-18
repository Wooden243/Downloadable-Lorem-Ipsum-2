# index.php — 全文・逐次解説

## ① ファイル概要

ルートURLからフォームへ302リダイレクトする小さなHTTPエントリポイント。

**場所**：`index.php`。
**入出力と他ファイルの関係**：ブラウザGET /を GET /generate.php へ誘導する。

**前提知識**：302はリダイレクトであってHTML描画ではない, Locationヘッダーは応答本文とは異なる, フォームに直接アクセスしても動作する。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `index.php` の全文で、生成時のSHA-256は `6bba427ae4dd61c1cb0060fac9f0a15c12e3b17ac784d7295eb64c1534ff5821`。

```php
<?php
declare(strict_types=1);

// Keep the root URL convenient while preserving the requested generate.php path.
header('Location: /generate.php', true, 302);
```

## ③ 上から下へ — 逐次読解

**行番号は上記ソースコードの行番号に一致する。** 各行の構文・必要性・変更時の影響を確認する。

### 行 1〜5

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 1 | `<?php` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 2 | `declare(strict_types=1);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 3 | `(空行)` | 空行。論理的な段落の境界。 |
| 4 | `// Keep the root URL convenient while preserving the requested generate.php path.` | 開発者向けの説明・前提・コメント。処理そのものには影響しない。 |
| 5 | `header('Location: /generate.php', true, 302);` | HTTPレスポンスヘッダーを設定。本文のechoより前に呼ぶ必要がある。 |

## ④ 実行トレース

GET / → HTTP 302 Location:/generate.php → GET /generate.php。

## ⑤ 最重要部分の再確認

1. 302はリダイレクトであってHTML描画ではない。
2. Locationヘッダーは応答本文とは異なる。
3. フォームに直接アクセスしても動作する。
4. 管理画面をpublic rootに置く設計。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

