# 02 KEY CONCEPTS — HTTP・フォーム・OOP・セキュリティ

## 1. HTTPメソッド GET / POST【必須】

**意味**：HTTPのリクエストでサーバーに要求する操作の種別。GETは一般にリソースの取得、POSTはデータを渡してサーバー側の処理を求める用途に使う。

- **今回の役割**：`GET /generate.php` で入力ページを取得、`POST /download.php` で生成条件を送信。
- **該当コード**：`generate.php` の `method="post"`、`download.php` の `$_SERVER['REQUEST_METHOD']`。
- **例**：`curl -i -X POST http://localhost:8000/download.php -d 'count=5&format=json'`。
- **よくある誤解**：POST自体が暗号化を保証するわけではない。実環境の秘匿にはHTTPSが必要。GETだから必ず安全、というわけでもない。
- **実務**：REST API設計、検索フォーム、データ作成・更新、エクスポート処理。

## 2. HTMLフォーム・name・action【必須】

`<form action="/download.php" method="post">` は送信先とHTTPメソッドを決める。`<input name="count" value="5">` の `name` がPHP側の `$_POST['count']` に対応する。`min` と `max` はブラウザ入力を支援するが、サーバー側の不正入力を防げない。

```html
<form method="post" action="/download.php">
  <input name="count" type="number" min="1" max="100" value="5">
  <select name="format"><option value="json">JSON</option></select>
  <button type="submit">Generate</button>
</form>
```

**よくある間違い**：`name` を省略するとPHPで正しいキーが取得できない。`id` と `name` は別の属性。`GET` にすると `$_POST` に値が入らない。

## 3. `$_POST` と `php://input`【必須】

`$_POST` は主にURLエンコードされたフォームの内容をPHPが連想配列化したもの。例：`['count' => '5','format' => 'json']`。`'5'` は文字列なので型検証が必要。

`file_get_contents('php://input')` は生のリクエスト本文（raw body）を読み込める。JSONペイロードで `Content-Type: application/json` を使うAPIなら、生データを `json_decode` する方式も考えられる。今回のHTMLフォームでは `$_POST` が適切であり、生JSONを解析する機能は要求されていない。

## 4. バリデーションと信頼境界【必須】

**意味**：入力されたデータが仕様どおりの型・範囲・集合に属するかを検査すること。`RequestValidator::integer()` は `/\A(?:0|[1-9][0-9]*)\z/D` で、負号・小数点・指数表記・先頭0・空文字を拒否する。その後 `(int)` にし、上下限で再検査する。

- **該当**：`src/Support/RequestValidator.php`。
- **理由**：HTTPは任意の値を送れるので、ブラウザの入力制限だけでは十分でない。
- **実務**：SQLインジェクション等を直接防ぐ万能策ではないが、型・範囲チェックは安全なWeb処理の第一段階。
- **誤解**：`is_numeric('2.5')` がtrueでも、今回の「整数件数」に合致しない。単なる `(int)` は `2.5` や `abc` を意図せず変換してしまう。

## 5. HTTPステータスコード【必須】

`200 OK` は成功、`405 Method Not Allowed` はGETなど許可していないメソッド、`412 Precondition Failed` はこの教材の指示に合わせた無効入力、`500 Internal Server Error` は想定外のサーバー障害。一般的なWeb APIでは入力値の不正に `400 Bad Request` や `422 Unprocessable Content` を採用する例もあるが、今回は **課題指示どおり 412** を使っている。

```php
http_response_code(412);
header('Content-Type: text/plain; charset=UTF-8');
echo "Invalid request\n";
```

## 6. `header()` と HTTPレスポンス【必須】

```php
header('Content-Type: application/json; charset=UTF-8');
header('Content-Disposition: attachment; filename="users.json"');
echo $body;
```

`Content-Type` はメディアタイプ、`Content-Disposition` は「表示するか」「添付ファイルとして保存するか」。headersは原則として `echo` によってレスポンス本文を出す前に送る必要がある。文字列そのものはレスポンス本文であり、ヘッダーとは異なる。

- **今回**：HTMLは `inline`、JSON/TXT/Markdownは `attachment`。
- **誤解**：`Content-Disposition` がファイルをサーバーのディスクへ書き出すわけではない。
- **実務**：CSVダウンロード、生成帳票、画像・PDF配信など。

## 7. クラス、継承、interface【必須】

```php
interface FileConvertible {
    public function toString(): string;
    public function toHTML(): string;
    public function toMarkdown(): string;
}

class Employee extends User implements FileConvertible {
    // Userの情報を引き継ぎ、従業員固有の情報を追加
}
```

- `extends` は「親クラスの性質を引き継ぐ」。`Employee extends User`、`RestaurantChain extends Company`。
- `implements` は「指定メソッドを実装する契約」。Interface自身はデータの中身や具象処理を直接作成しない。
- `private` はそのクラス内に情報を閉じ込めるカプセル化。
- `parent::__construct(...)` で親クラスの初期化を利用する。
- `Exporter` はどのモデルか知らなくても `toHTML()` 等を呼べる（ポリモーフィズム）。
- 実務ではドメインモデル、交換可能なストレージ層、通知サービス、データ変換モジュール等に応用する。

## 8. JSONシリアライズ【必須】

`toArray()` はモデルをPHPの連想配列へ変換する。`json_encode()` がPHP配列をJSONの文字列へ変換する。`JSON_THROW_ON_ERROR` で変換失敗を検知できる。`JSON_PRETTY_PRINT` は読みやすい整形、`JSON_UNESCAPED_UNICODE` は日本語を読みやすくそのまま出すオプション。

**誤解**：「JSON」はJavaScriptのオブジェクトそのものではない。HTTPで返すときは文字列/バイト列というデータ表現になる。配列内の `postalCode` は数値でなく文字列なので、`00005` が保たれる。

## 9. HTMLエスケープとXSS【必須】

HTML本文に外部由来の文字列をそのまま出すと、`<script>` 等の入力がブラウザでタグとして解釈されることがある。`Html::escape()` は `htmlspecialchars(..., ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8')` を使い、`<` や `&` をHTMLエンティティへ置き換える。

`<script>` → `&lt;script&gt;` となり、タグではなく文字として表示される。HTMLの属性値に出す場合も適切なエスケープが必要。CSSの `style-src 'unsafe-inline'` はこの教材の**静的HTMLプレビューのインラインCSS**を許容するための限定的な設定で、外部スクリプトは許可しない。

## 10. 乱数とテスト【重要】

`random_int($min, $max)` は指定区間から一様な整数を選ぶPHP標準関数。名前・年齢・店舗名などのランダムデータは実行ごとに変わる。そのためテストでは **具体的な出力値ではなく、不変条件**（件数、上下限、ID重複のなさ、型など）を確認する。

`$j % $locationCount` は余りを求める演算。従業員を店舗に交互に分配するラウンドロビン方式に使う。

## 11. Factoryメソッド【重要】

`RandomGenerator::users` や `RandomGenerator::chains` は、直接入力フォームやWebヘッダーを扱わず、オブジェクトを構築して返す「Factory」に相当する。生成処理を1カ所に集約すると、フォームからの呼び出しと自動テストで同じ振る舞いを使い回せる。

## 12. Composerとautoload【重要】

`require_once` は指定PHPファイルを一度だけ読み込む。`spl_autoload_register` は未読み込みクラスが使われたとき、クラス名からファイルパスへ対応付けて必要なファイルを読み込む。今回のコードは名前空間 `RestaurantMockup\` を `src/` 以下へマッピングする。Composerの `vendor/autoload.php` は必要に応じて使う外部ライブラリ・プロジェクトの自動読み込み機構だが、依存がない本課題では不可欠ではない。

## 13. セキュリティヘッダー【重要】

`X-Content-Type-Options: nosniff` はMIMEタイプの推測を抑える。`Cache-Control: no-store` はレスポンスの保存を抑制する意図を示す。`X-Frame-Options: DENY` と `frame-ancestors 'none'` は他ページへの埋め込みを抑える。これらはコードの責務を明確にする防御策だが、TLS・認証・CSRF対策等の包括的な代替にはならない。今回は永続的状態変更やログインがないモック生成である。

## 14. 言語と実行環境【参考】

`.php` はサーバー上で実行され、その結果のHTMLなどだけがブラウザへ送られる。HTML/CSSはブラウザで解釈される。`php -S 127.0.0.1:8000 -t .` は開発用HTTPサーバーを起動するコマンドであり、Ubuntu自体が自動的にWeb公開されるという意味ではない。
