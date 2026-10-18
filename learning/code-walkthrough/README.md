# Code walkthroughs — 主要ファイル全文と逐次解説

各ドキュメントには元ファイルの**省略なしの全文**、主要処理の説明、全行のコメント、および具体的な実行トレースを収録。ソース変更時は教材も更新すること。

- [`generate.php`](./generate.md) — ブラウザから利用者が操作する2つのHTMLフォームを表示するエントリポイント。値はURLエンコードされたPOST本文になる。
- [`download.php`](./download.md) — フォームから届くPOST要求だけを受け付けるHTTPエンドポイント。入力検証・モデル生成・エクスポートの各コンポーネントを呼び出してレスポンスを完成する。
- [`index.php`](./index.md) — ルートURLからフォームへ302リダイレクトする小さなHTTPエントリポイント。
- [`src/bootstrap.php`](./src__bootstrap.md) — RestaurantMockup名前空間のクラス名をsrc下のファイルへ対応づけるオートローダー。
- [`src/Contracts/FileConvertible.php`](./src__Contracts__FileConvertible.md) — モデルが共通して実装する3つの変換メソッドの約束を宣言する。
- [`src/Models/Company.php`](./src__Models__Company.md) — レストランチェーンが継承する会社共通属性を保持する。
- [`src/Models/Employee.php`](./src__Models__Employee.md) — Userに職種・給与・開始日・職務リストを追加する従業員モデル。
- [`src/Models/RestaurantChain.php`](./src__Models__RestaurantChain.md) — Companyを継承し、店舗オブジェクトの配列とチェーン固有の属性を持つ。
- [`src/Models/RestaurantLocation.php`](./src__Models__RestaurantLocation.md) — 住所と開店状態、配下の従業員一覧を持つ店舗モデル。
- [`src/Models/User.php`](./src__Models__User.md) — 名前・メール・IDを保持する基本ユーザーモデル。社員モデルの親クラス。
- [`src/Support/Exporter.php`](./src__Support__Exporter.md) — モデル配列をHTML/JSON/TXT/Markdownに変換し、MIMEタイプ・ダウンロードヘッダーも返す。
- [`src/Support/Html.php`](./src__Support__Html.md) — HTML出力とMarkdown出力の最低限の特殊文字処理を共通化する。
- [`src/Support/RandomData.php`](./src__Support__RandomData.md) — ダミーの名前、都市、電話番号、会社名などの小さな語彙集と無作為選択関数を集約する。
- [`src/Support/RandomGenerator.php`](./src__Support__RandomGenerator.md) — 送信値で指定された件数・範囲を正確に満たすUser/RestaurantChainのインスタンスを構築するファクトリー。
- [`src/Support/RequestValidator.php`](./src__Support__RequestValidator.md) — サーバー側の全フォーム入力を検証し、文字列を型付きパラメータへ変換する。
- [`tests/run.php`](./tests__run.md) — ブラウザなしでモデル、検証、変換、エスケープの条件を確認するPHPテストハーネス。
- [`tests/http_test.py`](./tests__http_test.md) — 実際のPHP開発サーバーを子プロセスとして起動し、HTTP GET/POST/ダウンロード応答を調べる結合テスト。
- [`scripts/start.sh`](./scripts__start.md) — カレントディレクトリをプロジェクトルートへ移してPHP開発サーバーを起動するWSL用補助スクリプト。
- [`assets/styles.css`](./assets__styles.md) — ブラウザで表示するフォームのレイアウト・色・余白・レスポンシブ対応を提供するCSS。
