# generate.php — 全文・逐次解説

## ① ファイル概要

ブラウザから利用者が操作する2つのHTMLフォームを表示するエントリポイント。値はURLエンコードされたPOST本文になる。

**場所**：`generate.php`。
**入出力と他ファイルの関係**：PHPによるHTML生成の結果がブラウザへ送られる。field() と formatSelect() はUI部品を組み立てる補助関数で、生成・検証自体は行わない。

**前提知識**：form actionとmethodの対応, name属性がPHPの$_POSTキーを決める, サーバー側でも必ず検証する。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `generate.php` の全文で、生成時のSHA-256は `7d2d411d8e1d5dc0bc958d51c2d97253509b2f728b72fe50f146e62fddba752b`。

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

use RestaurantMockup\Support\Html;

function field(string $label, string $name, string $value, int $min, int $max, string $note = ''): string
{
    return '<label class="form-field"><span>' . Html::escape($label) . '</span>'
        . '<input type="number" required name="' . Html::escape($name)
        . '" min="' . $min . '" max="' . $max . '" step="1" value="' . Html::escape($value) . '">'
        . ($note === '' ? '' : '<small>' . Html::escape($note) . '</small>') . '</label>';
}

function formatSelect(string $prefix): string
{
    return '<label class="form-field"><span>Output format</span><select name="format" id="' . $prefix . '-format">'
        . '<option value="html">HTML (view in browser)</option>'
        . '<option value="json">JSON (.json)</option>'
        . '<option value="txt">Plain text (.txt)</option>'
        . '<option value="md">Markdown (.md)</option>'
        . '</select></label>';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="Create sample user data or custom restaurant chain mockups and export as HTML, JSON, TXT or Markdown.">
  <title>Downloadable Lorem Ipsum (2) · Restaurant Chain Mockup Extension</title>
  <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
  <main class="container">
    <header class="site-header">
      <span class="eyebrow">RECURSION PROJECT · DYNAMIC WEB SERVERS</span>
      <h1>Downloadable Lorem Ipsum (2)</h1>
      <p>Generate fictional user profiles and restaurant chain records. Preview as HTML or download JSON, text, and Markdown.</p>
      <p class="hint">Form data is processed by PHP through a POST request; no user data is stored.</p>
    </header>
    <div class="form-grid">
      <section class="form-card" aria-labelledby="users-title">
        <div class="card-icon" aria-hidden="true">01</div>
        <h2 id="users-title">User profiles</h2>
        <p class="card-description">Generate a requested number of fictional User instances.</p>
        <form action="/download.php" method="post">
          <input type="hidden" name="dataset" value="users">
          <?= field('Number of users', 'count', '5', 1, 100, 'Between 1 and 100 users.') ?>
          <?= formatSelect('users') ?>
          <button type="submit">Generate user data <span aria-hidden="true">↗</span></button>
        </form>
      </section>
      <section class="form-card" aria-labelledby="chains-title">
        <div class="card-icon" aria-hidden="true">02</div>
        <h2 id="chains-title">Restaurant chain extension</h2>
        <p class="card-description">Customize staff count, pay range, locations and postal-code range per chain.</p>
        <form action="/download.php" method="post">
          <input type="hidden" name="dataset" value="chains">
          <div class="field-row">
            <?= field('Number of chains', 'chain_count', '2', 1, 10) ?>
            <?= field('Employees per chain', 'employee_count', '8', 1, 100) ?>
          </div>
          <?= field('Locations per chain', 'location_count', '3', 1, 20) ?>
          <div class="field-row">
            <?= field('Minimum annual salary (USD)', 'salary_min', '35000', 0, 1000000) ?>
            <?= field('Maximum annual salary (USD)', 'salary_max', '85000', 0, 1000000) ?>
          </div>
          <div class="field-row">
            <?= field('Minimum ZIP code', 'postal_min', '10000', 0, 99999) ?>
            <?= field('Maximum ZIP code', 'postal_max', '95000', 0, 99999) ?>
          </div>
          <?= formatSelect('chains') ?>
          <button type="submit">Generate restaurant chains <span aria-hidden="true">↗</span></button>
        </form>
      </section>
    </div>
    <section class="footnote"><h2>How it works</h2>
      <p><strong>1.</strong> Fill in a form. <strong>2.</strong> PHP validates the POST request. <strong>3.</strong> The generator constructs model objects. <strong>4.</strong> The exporter returns a preview or downloadable file.</p>
      <p>Validation includes HTTP 412 responses for missing, malformed or out-of-range values. All generated information is fictional.</p>
    </section>
    <footer>Restaurant Chain Mockup Extension · No external services or packages required</footer>
  </main>
</body>
</html>
```

## ③ 上から下へ — 逐次読解

**行番号は上記ソースコードの行番号に一致する。** 各行の構文・必要性・変更時の影響を確認する。

### 行 1〜10

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 1 | `<?php` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 2 | `declare(strict_types=1);` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 3 | `(空行)` | 空行。論理的な段落の境界。 |
| 4 | `require_once __DIR__ . '/src/bootstrap.php';` | 依存ファイルを一度だけ読み込む。未読み込みクラスの使用準備をする。 |
| 5 | `(空行)` | 空行。論理的な段落の境界。 |
| 6 | `use RestaurantMockup\Support\Html;` | クラス名のimport。FQCNを毎回書かず短いクラス名で参照できる。 |
| 7 | `(空行)` | 空行。論理的な段落の境界。 |
| 8 | `function field(string $label, string $name, string $value, int $min, int $max, string $note = ''): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 9 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 10 | `return '<label class="form-field"><span>' . Html::escape($label) . '</span>'` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `. '<input type="number" required name="' . Html::escape($name)` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 12 | `. '" min="' . $min . '" max="' . $max . '" step="1" value="' . Html::escape($value) . '">'` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 13 | `. ($note === '' ? '' : '<small>' . Html::escape($note) . '</small>') . '</label>';` | データをHTMLのテキスト/属性文脈に適した表現へエスケープする。 |
| 14 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 15 | `(空行)` | 空行。論理的な段落の境界。 |
| 16 | `function formatSelect(string $prefix): string` | 関数/メソッド定義。引数の型・戻り値の型・責務を確認する重要箇所。 |
| 17 | `{` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 18 | `return '<label class="form-field"><span>Output format</span><select name="format" id="' . $prefix . '-forma…` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 19 | `. '<option value="html">HTML (view in browser)</option>'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 20 | `. '<option value="json">JSON (.json)</option>'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `. '<option value="txt">Plain text (.txt)</option>'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 22 | `. '<option value="md">Markdown (.md)</option>'` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 23 | `. '</select></label>';` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 24 | `}` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 25 | `?>` | 構文を開く/閉じる区切り。前後の処理構造を確定する。 |
| 26 | `<!doctype html>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 27 | `<html lang="en">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 28 | `<head>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 29 | `<meta charset="utf-8">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 30 | `<meta name="viewport" content="width=device-width,initial-scale=1">` | フォームフィールド名を定義。サーバー側のPOST配列のキーに対応する。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `<meta name="description" content="Create sample user data or custom restaurant chain mockups and export as …` | フォームフィールド名を定義。サーバー側のPOST配列のキーに対応する。 |
| 32 | `<title>Downloadable Lorem Ipsum (2) · Restaurant Chain Mockup Extension</title>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 33 | `<link rel="stylesheet" href="/assets/styles.css">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 34 | `</head>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 35 | `<body>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 36 | `<main class="container">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 37 | `<header class="site-header">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 38 | `<span class="eyebrow">RECURSION PROJECT · DYNAMIC WEB SERVERS</span>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 39 | `<h1>Downloadable Lorem Ipsum (2)</h1>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 40 | `<p>Generate fictional user profiles and restaurant chain records. Preview as HTML or download JSON, text, a…` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `<p class="hint">Form data is processed by PHP through a POST request; no user data is stored.</p>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 42 | `</header>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 43 | `<div class="form-grid">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 44 | `<section class="form-card" aria-labelledby="users-title">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 45 | `<div class="card-icon" aria-hidden="true">01</div>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 46 | `<h2 id="users-title">User profiles</h2>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 47 | `<p class="card-description">Generate a requested number of fictional User instances.</p>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 48 | `<form action="/download.php" method="post">` | フォーム送信方式をHTTP POSTに設定。GETとは本文/URLの扱いが変わる。 |
| 49 | `<input type="hidden" name="dataset" value="users">` | フォームフィールド名を定義。サーバー側のPOST配列のキーに対応する。 |
| 50 | `<?= field('Number of users', 'count', '5', 1, 100, 'Between 1 and 100 users.') ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `<?= formatSelect('users') ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 52 | `<button type="submit">Generate user data <span aria-hidden="true">↗</span></button>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 53 | `</form>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 54 | `</section>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 55 | `<section class="form-card" aria-labelledby="chains-title">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 56 | `<div class="card-icon" aria-hidden="true">02</div>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 57 | `<h2 id="chains-title">Restaurant chain extension</h2>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 58 | `<p class="card-description">Customize staff count, pay range, locations and postal-code range per chain.</p>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 59 | `<form action="/download.php" method="post">` | フォーム送信方式をHTTP POSTに設定。GETとは本文/URLの扱いが変わる。 |
| 60 | `<input type="hidden" name="dataset" value="chains">` | フォームフィールド名を定義。サーバー側のPOST配列のキーに対応する。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `<div class="field-row">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 62 | `<?= field('Number of chains', 'chain_count', '2', 1, 10) ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 63 | `<?= field('Employees per chain', 'employee_count', '8', 1, 100) ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 64 | `</div>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 65 | `<?= field('Locations per chain', 'location_count', '3', 1, 20) ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 66 | `<div class="field-row">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 67 | `<?= field('Minimum annual salary (USD)', 'salary_min', '35000', 0, 1000000) ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 68 | `<?= field('Maximum annual salary (USD)', 'salary_max', '85000', 0, 1000000) ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 69 | `</div>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 70 | `<div class="field-row">` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |

### 行 71〜80

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `<?= field('Minimum ZIP code', 'postal_min', '10000', 0, 99999) ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 72 | `<?= field('Maximum ZIP code', 'postal_max', '95000', 0, 99999) ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 73 | `</div>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 74 | `<?= formatSelect('chains') ?>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 75 | `<button type="submit">Generate restaurant chains <span aria-hidden="true">↗</span></button>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 76 | `</form>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 77 | `</section>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 78 | `</div>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 79 | `<section class="footnote"><h2>How it works</h2>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 80 | `<p><strong>1.</strong> Fill in a form. <strong>2.</strong> PHP validates the POST request. <strong>3.</stro…` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |

### 行 81〜86

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 81 | `<p>Validation includes HTTP 412 responses for missing, malformed or out-of-range values. All generated info…` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 82 | `</section>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 83 | `<footer>Restaurant Chain Mockup Extension · No external services or packages required</footer>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 84 | `</main>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 85 | `</body>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |
| 86 | `</html>` | HTMLマークアップ。構造、属性（name, actionなど）とフォームの送信を確認する。 |

## ④ 実行トレース

GETで開く。User数5、JSONを選択して送信すると、count=5,format=json,dataset=usersがPOSTされる。

## ⑤ 最重要部分の再確認

1. form actionとmethodの対応。
2. name属性がPHPの$_POSTキーを決める。
3. サーバー側でも必ず検証する。
4. field()で範囲を表示しても信用境界を越えない。
5. submit時にはdownload.phpに処理を委譲する。
6. HTMLとPHPを同じファイルで構成できる。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

