# 00 START HERE — Downloadable Lorem Ipsum (2)

## このプログラムを3分で理解する

これは「Webフォームでダミーデータの件数・条件・出力形式を入力 → HTTP POSTでPHPに送る → サーバー側でチェック → オブジェクトを生成 → HTMLプレビューまたはダウンロードファイルを返す」アプリです。

```text
ブラウザで generate.php を開く（GET）
  → User / Restaurant Chain のどちらかを選ぶ
  → 件数・給与・店舗数・郵便番号・形式を入力
  → フォームを送信（POST /download.php）
  → RequestValidator がすべてチェック
      不適切なら HTTP 412（生成しない）
  → RandomGenerator が User または RestaurantChain を構築
  → Exporter が toArray/toString/toHTML/toMarkdown を呼び分ける
  → PHP header() が Content-Type / Content-Disposition を設定
  → ブラウザで HTML 表示、その他はファイル保存
```

**最重要の4ファイル**：`generate.php`（フォーム）、`download.php`（HTTP処理）、`src/Support/RequestValidator.php`（妥当性チェック）、`src/Support/Exporter.php`（表示形式）。データ構造の中心は `src/Support/RandomGenerator.php` と `src/Models/` です。

## 最低限この7つを覚える

1. `GET` は主にページ取得、`POST` はフォームの値などをサーバーに送信する際に使う。
2. `<form method="post" action="/download.php">` で POST先を決める。
3. `$_POST['count']` は、標準的な `application/x-www-form-urlencoded` のPOSTをPHPが解析した結果を表す。任意の JSON POST 本文が入るとは限らない。
4. ユーザーが送る値は信用しない。`min="1"` はブラウザ上の支援にすぎないので、PHPも検証する。
5. `FileConvertible` は「複数の表現へ変換できる」という**メソッドの約束**であり、実際の変換方法はモデルごとに異なる。
6. `Content-Type` は中身の種類、`Content-Disposition` はインライン表示かダウンロードかを示す。
7. **PHPオブジェクトと JSON テキストは別物**。`toArray()` で配列化してから `json_encode()` で JSON 表現へ変換する。

## 学習の優先度

| 技術・考え方 | SWE | Backend | Frontend | AI/ML周辺 |
|---|---|---|---|---|
| HTTP GET/POST・ステータス・ヘッダー | 必須 | 必須 | 重要 | 重要 |
| 入力検証と出力エスケープ | 必須 | 必須 | 必須 | 重要 |
| クラスと継承、インターフェース | 必須 | 必須 | 重要 | 重要 |
| シリアライズ・JSON・ファイルダウンロード | 必須 | 必須 | 重要 | 重要 |
| HTMLフォームの構文 | 重要 | 重要 | 必須 | 参考 |
| 細かなCSSレイアウト | 参考 | 参考 | 重要 | 参考 |

これらは**この技術の職種別の一般的な関連度**であって、学習上の評価や経歴を意味するものではありません。

## 15分で理解するコース

1. このファイルを読む（3分）。
2. `learning/03_EXECUTION_FLOW.md` の「ユーザー3人をJSONでダウンロード」のトレースを読む（5分）。
3. `download.php` を開き、`validate → generate → render → header/echo` を説明する（7分）。

## 30分で理解するコース

15分コースに加え：`RequestValidator::validate()` の `integer()` / `choice()` を読む、`Exporter::render()` の4分岐を読む、`php tests/run.php` を実行する。

## 60分で理解するコース

30分コースに加え：`RandomGenerator::chains()` と `FileConvertible`、User/Employee と Company/RestaurantChain の継承関係を追い、`learning/05_REVIEW.md` の確認問題を解く。

## ファイル閲覧のおすすめ順序

`generate.php` → `download.php` → `RequestValidator.php` → `RandomGenerator.php` → `Exporter.php` → `User.php` → `RestaurantChain.php` → `RestaurantLocation.php` → `Employee.php` → `Company.php` → `Html.php` → `RandomData.php` → `tests/`。

## 今は暗記しなくてよい

- ランダムな名前の候補文字列やCSSのすべてのプロパティ。
- 個々の例外メッセージの正確な文言。
- `header()` の珍しいオプションやステータス番号の網羅暗記。
- 生成したコードを1行ずつ暗記すること。**流れを自分の言葉で説明できることを優先**。

## 実行の入り口

```bash
php -S 127.0.0.1:8000 -t .
# ブラウザで http://localhost:8000/generate.php
# 停止: Ctrl+C
```

学習問題は `05_REVIEW.md`、実物コードの全行解説は `code-walkthrough/` を参照してください。
