# 03 EXECUTION FLOW — リクエストと値のトレース

## A. ユーザー3件をJSONで出力する

1. `generate.php` の User フォームで件数を3、形式をJSONと選ぶ。
2. `<form method="post" action="/download.php">` により本文に `dataset=users&count=3&format=json` が入る。
3. PHPがURLエンコードを復号し `$_POST = ['dataset'=>'users', 'count'=>'3', 'format'=>'json']` を用意する。**この時点では count は文字列**。
4. `download.php` のメソッド検査は `'POST' === 'POST'` なので通過する。
5. `RequestValidator::validate` が `format` を `['html','json','txt','md']` に照合し、`dataset` を `users` と認識する。
6. `integer($post,'count',1,100)` は正規表現で `'3'` が十進数の整数表記か確認し、`(int)'3'` で3に変換し、上下限を確認する。
7. `$parameters` は `['dataset'=>'users', 'format'=>'json','count'=>3]` になる。
8. `RandomGenerator::users(3,3)` で `random_int(3,3)` は3、ループで `User(1,...), User(2,...), User(3,...)` が生成される。
9. `Exporter::render` の `json` 分岐で、各Userの `toArray()` から `[['id'=>1,...], ...]` が作られる。
10. `json_encode` によりJSONテキスト化する。例：

```json
[
  {"id":1,"name":"Avery Reed","email":"user1@example.com"},
  {"id":2,"name":"Robin Kim","email":"user2@example.com"},
  {"id":3,"name":"Jordan Santos","email":"user3@example.com"}
]
```

上の名前は説明用の例であり、実行ではランダムです。

11. `Content-Type: application/json; charset=UTF-8` と `Content-Disposition: attachment; filename="users.json"` が付く。
12. ブラウザが `users.json` をダウンロードする。サーバー側にファイルを作成しているわけではない。

## B. 2チェーン、各6社員、3店舗、給与40000～60000、ZIP10000～90000

ブラウザは次を送る：

```text
dataset=chains
format=md
chain_count=2
employee_count=6
location_count=3
salary_min=40000
salary_max=60000
postal_min=10000
postal_max=90000
```

`RequestValidator` はこれらをすべてintへ変換し、給与範囲と郵便番号範囲が逆転していないか確認する。次に `RandomGenerator::chains(2,6,3,40000,60000,10000,90000)` を呼ぶ。

### Factoryの内部ループ（重要）

```text
$i = 0       → 1つ目の RestaurantChain、chainId 1000
  $j=0..2    → Locationを3つ生成
  $j=0..5    → Employeeを6人生成
                店舗index = $j % 3
                0, 1, 2, 0, 1, 2
                → 各店舗に2人ずつ
  各店舗をchainに追加
$i = 1       → 2つ目の RestaurantChain、chainId 1001
  同じ手順を繰り返す
```

ユーザー指定の給与範囲は `random_int(40000,60000)`、ZIPコードは `str_pad((string)random_int(10000,90000),5,'0',STR_PAD_LEFT)`。ZIPを文字列として保存すると、範囲が `5..5` なら `00005` のように先頭の0が消えない。

結果は `list<RestaurantChain>` という配列で、`RestaurantChain` は `RestaurantLocation` の配列を、各Locationは `Employee` の配列を持つ。Markdownでは各モデルの `toMarkdown()` を合成し、ヘッダー付き `.md` にする。

## C. 不正なcount=0

```text
POST: dataset=users&count=0&format=json
    ↓
integer(...,1,100) は0が下限未満と判定
    ↓
InvalidArgumentException 発生
    ↓
download.php の catch が受け取る
    ↓
HTTP 412 / Content-Type: text/plain
    ↓
Invalid request: count must be between 1 and 100.
```

不適切なパラメータでは、**乱数を作らず、ファイルダウンロードヘッダーも発行しない**。

## D. なぜHTMLだけブラウザ表示か

`format=html` のとき、Exporter は `text/html` と `inline` を返す。一方、JSON/TXT/MDでは `attachment; filename=...` になる。同じPOSTでもレスポンスの **本文（body）とメタ情報（headers）** が変わる。

## E. 重要な確認

実装は、入力検証・生成・シリアライズをそれぞれ独立させている。バグを見つけたら、(1)フォーム名と`$_POST`、(2)`validate`後の型、(3)`RandomGenerator`での個数、(4)`Exporter`のフォーマット、(5)レスポンスヘッダー、の順で切り分けると効率が良い。
