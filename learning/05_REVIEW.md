# 05 REVIEW — 問題と詳しい模範解答

## 第1部：基本用語

### 問1　`GET` と `POST` の違いを、本アプリを例に説明する。

**解答**：`GET /generate.php` は入力ページをサーバーから取得する。`POST /download.php` はフォームに入力した件数・形式などをリクエスト本文で送信し、サーバーにデータ生成と表現変換を依頼する。POSTは必ず永続化する操作という意味ではなく、本アプリは保存しない生成処理に使っている。

### 問2　`name="count"` と `id="count"` の役割は同じか。

**解答**：異なる。`name` はフォーム送信でキーとして使用され、PHPの `$_POST['count']` へ対応する。`id` はHTML内部の一意な識別や `<label for>` の接続、JavaScriptによるDOM参照などに使われる。

### 問3　`Content-Type` と `Content-Disposition` の違い。

**解答**：`Content-Type` は `application/json`、`text/html` など本文の種類を知らせる。`Content-Disposition: attachment; filename="users.json"` は保存ファイルとして扱うことと名前を提示する。`inline` はブラウザで表示する想定。

### 問4　`toArray()` と `json_encode()` の違い。

**解答**：`toArray()` はクラスのプロパティをPHP連想配列という内部データ構造へ組み替える。`json_encode()` はその配列を、HTTP本文として送れるJSONテキストへ符号化する。前者は独自のモデル変換、後者は標準的なJSONシリアライズである。

## 第2部：コード予測

### 問5　`RequestValidator::validate(['count'=>'3','format'=>'json'])` の返り値は？

**解答**：`['dataset'=>'users','format'=>'json','count'=>3]`。`dataset` は明示されなければusersを選択し、`count` は数字文字列 `'3'` からPHPの整数 `3` に変換される。`format` は許可済み文字列のまま。

### 問6　`RequestValidator::validate(['count'=>'1e2','format'=>'json'])` はどうなるか。

**解答**：`InvalidArgumentException` が発生する。`is_numeric` なら指数表記も受け入れ得るが、今回の `integer()` は十進整数表記だけを通す正規表現を使う。`download.php` 経由なら例外をcatchしてHTTP 412を返す。

### 問7　`RandomGenerator::chains(1,7,3,40000,50000,5,5)` で店舗ごとの従業員数は？

**解答**：ラウンドロビンで1店舗目3人、2店舗目2人、3店舗目2人になる。`$j % 3` が `0,1,2,0,1,2,0` の順に変化するため。給与は各自40000～50000、ZIPはすべて文字列 `00005`。

### 問8　なぜ `str_pad` で郵便番号を処理するのか。

**解答**：郵便番号は足し算の対象ではなく、先頭の0も意味を持つ識別文字列だから。整数 `5` だけで保存すると `00005` が復元されない。`str_pad((string)5,5,'0',STR_PAD_LEFT)` によって桁数が5になる。

## 第3部：処理説明

### 問9　ユーザーがJSON形式を指定してからダウンロードされるまでを説明する。

**解答例**：ブラウザで `generate.php` のUserフォームを送信 → POST本文が `/download.php` へ渡る → `$_POST` が作られる → `RequestValidator` がcount/formatを検証し、countを整数化 → `RandomGenerator::users($n,$n)` がn個のUserを生成 → `Exporter` がUserごとの `toArray()` を集めてJSONへエンコード → レスポンスの`Content-Type: application/json`と`Content-Disposition: attachment`を設定 → 本文を出す。ブラウザは `users.json` を保存する。

### 問10　不正入力なのに生成を先に実行すると何が問題か。

**解答**：大量件数など不正な条件を先に処理すると、メモリ・CPUを無駄に消費したり、意図しない値を出力したりする可能性がある。`validate` を成功させてからFactoryを呼ぶ「入力検証→生成」の順序が重要。

## 第4部：小規模改修

### 問11　JSONを整形なしの1行にしたい。どこを変更すべきか。

**解答**：`src/Support/Exporter.php` の `json_encode` オプションから `JSON_PRETTY_PRINT` を外す。モデル生成や入力検証を変える必要はない。変更後はJSONが依然パースできるか、件数とフィールドが維持されるかを確認する。

### 問12　新しい `csv` 出力を追加するには？

**解答例**：`RequestValidator::FORMATS` に `csv` を追加し、`generate.php` の両方のセレクトに選択肢を加え、`Exporter::render` にCSV分岐を実装する。CSVは引用符、カンマ、改行などのエスケープが必要で、`fputcsv()` の利用が適切。`Content-Type: text/csv` と `attachment; filename="...csv"` を付け、HTTPテストでダウンロードと内容を検証する。既存フォーマットのテストも再実行する。

### 問13　HTMLで新しい項目を表示するにはどこを修正する？

**解答**：対象Modelの `toHTML()` で安全にHTMLエスケープした値を追加する。JSONにも出すなら `toArray()`、Markdownにも出すなら `toMarkdown()`、TXTにも出すなら `toString()` に追加する。それぞれ別の表現を持つので、1か所変えるだけですべてのフォーマットへ自動反映されるとは限らない。

## 第5部：デバッグ演習

### 問14　`POST /download.php` が405になる。原因と確認手順は？

**解答**：ブラウザでURLを直接開くなどGETになっている可能性が高い。DevTools NetworkのRequest Methodを確認する。`generate.php` のフォームが `method="post"` と `action="/download.php"` になっているかを確認し、`curl -i -X POST ... -d ...` と比較する。

### 問15　ダウンロードしたJSONの先頭に Warning が出る。何が問題か？

**解答**：PHPがJSON本文より前にエラー表示などを出力し、JSONの構文が壊れた可能性がある。`php -l` で構文確認し、PHPのエラーログを調べる。不要な `echo` / `var_dump` を削除し、HTTP応答として出すものを `Exporter` の本文だけにする。エラー詳細はクライアントへ生出力しない。

### 問16　店舗を5に増やしたのに総社員数が5倍になるバグが発生。どこを確認する？

**解答**：`RandomGenerator::chains()` の社員生成ループが「店舗ループ」の内側に入っていないかを確認する。現行実装はまずLocationを5つ作り、その後別ループで合計社員数ぶん社員を作成し、`$j % $locationCount` で各店舗へ分配する。社員生成を各Locationで繰り返すと「社員数/チェーン」と「社員数/店舗」を取り違える。

## 自己評価の基準

- 8問以上答えられる：フォーム→サーバーの基礎を把握。
- 12問以上答えられる：実行と主要構造の理解が進んでいる。
- 全16問をコードを見ずに説明できる：変更点に応じて適切なファイルを特定する見通しがつく。

この基準は学習の目安であり、採用や評価制度に基づくスコアではありません。
