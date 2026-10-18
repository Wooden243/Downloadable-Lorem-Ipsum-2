# 04 DEBUGGING — トラブルシューティング

## 1. サーバーが起動しない

- `php: command not found`：PHP CLIがインストールされていない。WSL Ubuntuならパッケージ管理者から適切なPHP CLIを導入し、`php -v` を確認。
- `Failed to listen on 127.0.0.1:8000`：ポート重複。別ポートで `php -S 127.0.0.1:8080 -t .`。
- `404 Not Found`：実行ディレクトリが誤っていることが多い。`pwd` と `ls generate.php download.php` を確認。

## 2. ブラウザにコード自体が表示される

`file:///.../generate.php` で直接開いてもPHPは動作しない。`php -S ...` を起動して `http://localhost:8000/generate.php` からアクセスする。GitHub PagesもPHPの実行サーバーではない。

## 3. POSTがHTTP 405になる

送信先へGETした可能性が高い。フォームに `method="post"` があるか、ブラウザで `download.php` のURLを手入力していないか確認。

```bash
curl -i -X POST http://localhost:8000/download.php -d 'dataset=users&count=5&format=json'
```

## 4. HTTP 412が返る

`Invalid request:` で始まるメッセージは入力チェックによるもの。`RequestValidator::validate` → `integer` → `choice` の順に追う。

| Error | Cause | Fix |
|---|---|---|
| `Invalid or missing format` | `format` 未送信 / json以外など | `html`/`json`/`txt`/`md` のいずれかを送る |
| `Missing or invalid count` | `count` がない、または配列 | `count=5` のように単一の整数文字列で送る |
| `count must be a whole number` | 小数・指数・空欄・先頭ゼロ | `5` などの整数を書く |
| `count must be between 1 and 100` | 0や101など | 1～100へ変更 |
| `salary_min cannot exceed salary_max` | 範囲が逆 | 下限を上限以下にする |
| `postal_min cannot exceed postal_max` | ZIP範囲が逆 | 下限を上限以下にする |

## 5. JSONが壊れる・余分な文字が混ざる

レスポンス本文を出す前に `echo`、`var_dump`、PHP Warningなどを出していないか確認。`download.php` は `header()` を設定してから `echo $export['body']` を行う。デバッグ出力はレスポンスを壊すことがあるので、必要なら `error_log()` を利用する。`json_decode()` でJSONとして有効かテストする。

## 6. CSVやZIPがない

課題で指定された出力形式はHTML、JSON、TXT、Markdownの4種類。CSV/ZIP形式は実装していない。生成物一式のZIPと、アプリ実行中のデータダウンロードは別のもの。

## 7. 拡張時にメソッドが見つからない

- `Class not found`：`src/bootstrap.php` がrequireされているか、名前空間とディレクトリ名が一致しているかを確認。
- `Call to undefined method ...toArray()`：対象Modelへ `toArray()` が正しく追加されているか、`Exporter` から返す型が期待どおりかを確認。
- `Undefined array key`：生の `$_POST` にキーがない。バリデータで検証してから値を参照する。

## 8. ブラウザの開発者ツールを使う

DevTools → Network → フォーム送信 → `download.php` → Request Payload/Form Data と Response Headers を確認する。期待するキーが送信されているか、`Content-Type` と `Content-Disposition` が正しいか、HTTP status が200/412/405のどれかを確認する。

## 9. 単体テストとHTTPテスト

```bash
php tests/run.php
python3 tests/http_test.py
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

まず失敗したテストだけを絞り、モデルか入力かHTTPかを切り分ける。例えば `RandomGenerator` の人数検証が成功しHTTP POSTのJSON件数だけ失敗するなら、`download.php` の入力やExporter側の問題を疑う。

## 10. 実務的なデバッグ順序

(1) 再現条件を固定 → (2) 実際の入力を確認 → (3) エラーの種類を分類 → (4) 最小限の再現テストを追加 → (5) 小さく修正 → (6) 単体テスト・結合テスト・関係ない部分の回帰テスト → (7) 必要ならREADME/教材を同期更新。
