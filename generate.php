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
