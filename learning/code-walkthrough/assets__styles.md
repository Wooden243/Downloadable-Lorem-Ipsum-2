# assets/styles.css — 全文・逐次解説

## ① ファイル概要

ブラウザで表示するフォームのレイアウト・色・余白・レスポンシブ対応を提供するCSS。

**場所**：`assets/styles.css`。
**入出力と他ファイルの関係**：gridとflexを使い、画面幅で1列/2列に切り替える。入力フォームの実際の送信・検証機能はCSSとは独立。

**前提知識**：containerで中央揃え, form-gridで2カラム, field-rowでラベルと入力欄を調整。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `assets/styles.css` の全文で、生成時のSHA-256は `f9597cd7565227e49838e3f08ec4ec0cfbb404543802b9c91a69d0d615fc4ac1`。

```css
:root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#1d2d42;background:#eef3f9;font-size:16px}*{box-sizing:border-box}body{margin:0;background:linear-gradient(125deg,#f4f8fd,#e9f2fb);min-height:100vh}.container{max-width:1120px;margin:48px auto;padding:38px 42px;background:#fff;border:1px solid #e1e9f3;border-radius:18px;box-shadow:0 22px 58px #153f6811}.site-header{margin-bottom:36px;border-bottom:1px solid #e9edf4;padding-bottom:20px}.site-header h1{font-size:clamp(1.7rem,3vw,2.4rem);margin:10px 0 12px;letter-spacing:-.04em}.site-header p{color:#526579;max-width:850px;line-height:1.65}.eyebrow{color:#2674cf;font-size:.77rem;font-weight:800;letter-spacing:.14em}.hint{font-size:.89rem}.form-grid{display:grid;grid-template-columns:1fr 1.12fr;gap:22px;align-items:start}.form-card{border:1px solid #dfe8f3;background:#fff;border-radius:15px;padding:27px;box-shadow:0 7px 18px #1a4d8010}.card-icon{width:46px;height:46px;background:#e7f0ff;border-radius:13px;display:grid;place-items:center;font-size:.87rem;color:#245eb3;font-weight:800}.form-card h2{font-size:1.3rem;letter-spacing:-.03em;margin:14px 0 4px}.card-description{color:#5b6e83;font-size:.93rem;line-height:1.55;min-height:50px}.form-card form{display:flex;flex-direction:column;gap:18px;margin-top:20px}.form-field{display:flex;flex-direction:column;gap:7px;min-width:0;font-size:.87rem;font-weight:700;color:#33445a}.form-field input,.form-field select{width:100%;border:1px solid #d1ddea;border-radius:9px;height:43px;padding:0 12px;background:white;color:#20364e;font-size:.95rem;outline-offset:2px}.form-field input:focus,.form-field select:focus{border-color:#2978d5;outline:2px solid #b7d7ff}.form-field small{font-weight:400;color:#6c7c90}.field-row{display:grid;grid-template-columns:1fr 1fr;gap:13px}button{cursor:pointer;background:#2068c7;color:white;border:0;border-radius:10px;padding:14px 18px;font-weight:700;font-size:.94rem;text-align:left;display:flex;align-items:center;justify-content:space-between}button:hover{background:#1554a7}.footnote{margin-top:32px;background:#f3f7fd;border:1px solid #e2eaf6;border-radius:14px;padding:15px 24px}.footnote h2{font-size:1.05rem}.footnote p{color:#43556b;line-height:1.7}footer{text-align:center;color:#8292a6;font-size:.85rem;padding:28px 0 0}@media(max-width:800px){.container{margin:14px;padding:22px 16px}.form-grid{grid-template-columns:1fr}.field-row{grid-template-columns:1fr 1fr}.form-card{padding:20px}}@media(max-width:420px){.field-row{grid-template-columns:1fr}}
```

## ③ 上から下へ — 逐次読解

**行番号は上記ソースコードの行番号に一致する。** 各行の構文・必要性・変更時の影響を確認する。

### 行 1〜1

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 1 | `:root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;colo…` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

## ④ 実行トレース

画面幅800px以下ではform-gridが1列になり、スマートフォンで入力しやすくなる。

## ⑤ 最重要部分の再確認

1. containerで中央揃え。
2. form-gridで2カラム。
3. field-rowでラベルと入力欄を調整。
4. buttonのhoverスタイル。
5. :focusでキーボード操作中の視認性。
6. @mediaでレスポンシブ対応。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

