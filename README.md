# Downloadable Lorem Ipsum (2)

A PHP **POST-driven mock-data generator** based on the Recursion "Downloadable Lorem Ipsum(2)" task. It expands the earlier **Restaurant Chain Mockup** with a configurable HTML form, strict server-side validation and exports in HTML, JSON, plain text and Markdown. All sample names and companies are fictional. The project uses only the PHP standard library; there are no dependencies or API keys.

## Features

1. `generate.php`: two HTML forms using `method="post"` and `action="/download.php"`.
2. **User generation**: `count` (1–100) User records from `RandomGenerator::users($count, $count)`.
3. **Restaurant Chain Mockup extension**: configure chain count, total employees per chain, locations per chain, salary range and ZIP/postal code range. Generated records retain the earlier class hierarchy (`RestaurantChain extends Company`, `Employee extends User`, `FileConvertible` interface).
4. **Output formats**: `html` (browser preview), `json`, `txt`, `md` (file download). Each returns a correct content type; downloads use `Content-Disposition`.
5. **Server-side validation**: HTTP `412 Precondition Failed` for missing/invalid fields, numeric ranges, unsupported formats or malformed array parameters; `405 Method Not Allowed` for non-POST download requests.
6. Escaped HTML/Markdown, explicit JSON structures, sample-only data, no database or persistence, safe fixed filenames.

## Requirements and setup (Windows + WSL2 Ubuntu)

- PHP **8.1 or newer** recommended (PHP 8.4.24 used in verification). The project uses native PHP and no Composer packages.
- WSL2 Ubuntu or other system with `php` on PATH.
- A browser. Python 3 is **optional**, only for the separate HTTP integration test.

```bash
# After extracting the ZIP, enter the extracted directory in WSL Ubuntu:
cd /path/to/Downloadable-Lorem-Ipsum-2
php --version
php -S 127.0.0.1:8000 -t .
```

Visit <http://localhost:8000/generate.php> and submit either form. You can also run `bash scripts/start.sh` instead of the last command. Stop the server with **Ctrl+C**.

If port 8000 is occupied, use `php -S 127.0.0.1:8080 -t .` and visit `http://localhost:8080/generate.php`.

### Why there is no `vendor/autoload.php`

The assignment's **illustrative** `download.php` uses Composer's `vendor/autoload.php` plus `User.php` and `RandomGenerator.php`. This finished project has no third-party dependencies and already includes a PSR-4-style local autoloader in `src/bootstrap.php`. Therefore, `require_once __DIR__ . '/src/bootstrap.php'` replaces the Composer include. Classes reside in `src/Models/` and `src/Support/`, rather than flat top-level files. The `RandomGenerator::users($count, $count)` call is preserved.

## Use examples

### Generate 5 users as JSON

```bash
curl -i -X POST http://localhost:8000/download.php \
  -d 'dataset=users&count=5&format=json'
```

To save it as a file:

```bash
curl -fsS -X POST http://localhost:8000/download.php \
  -d 'dataset=users&count=5&format=json' -o users.json
```

### Generate 2 chains with 6 staff and 3 locations each

```bash
curl -fsS -X POST http://localhost:8000/download.php \
  -d 'dataset=chains&chain_count=2&employee_count=6&location_count=3&salary_min=40000&salary_max=60000&postal_min=10000&postal_max=90000&format=md' \
  -o restaurant-chains.md
```

For HTML preview use `format=html` in the web form, which renders a new full HTML page with expandable chain and location details. The `json`, `txt` and `md` choices prompt a file download with an appropriate filename.

### Error examples

```bash
curl -i -X POST http://localhost:8000/download.php -d 'count=0&format=json'
# HTTP 412, invalid count

curl -i http://localhost:8000/download.php
# HTTP 405, Allow: POST
```

The server enforces integer decimal strings, so `2.5`, `1e2`, `count[]=5`, count `0` or `101`, unsupported formats, reversed ranges and missing fields are rejected. Client-side min/max input attributes are **not** a substitute for these checks.

## Project structure

```text
Downloadable-Lorem-Ipsum-2/
├── generate.php                  # GET HTML forms
├── download.php                  # POST validate → generate → serialize → respond
├── index.php                     # Redirect to /generate.php
├── assets/styles.css             # Responsive form styling
├── src/
│   ├── bootstrap.php            # Standard-library autoloader
│   ├── Contracts/FileConvertible.php
│   ├── Models/
│   │   ├── User.php, Employee.php, Company.php
│   │   └── RestaurantChain.php, RestaurantLocation.php
│   └── Support/
│       ├── RandomGenerator.php  # User/chain fixture creation
│       ├── RequestValidator.php # Strict input checks
│       ├── Exporter.php         # JSON/HTML/TXT/Markdown representation
│       ├── RandomData.php       # Mock vocabulary / random values
│       └── Html.php             # Escaping helpers
├── tests/run.php                # Pure PHP unit and end-to-end model tests
├── tests/http_test.py           # Python standard-library HTTP tests (optional)
├── docs/requirements.md        # Traceable requirement coverage
├── docs/assumptions.md         # Design choices and task/sample discrepancies
├── docs/test-report.md         # Real verification outcomes and limitations
├── learning/
│   ├── 00_START_HERE.md         # 3-minute explanation & study tracks
│   ├── 01_ARCHITECTURE.md
│   ├── 02_KEY_CONCEPTS.md
│   ├── 03_EXECUTION_FLOW.md
│   ├── 04_DEBUGGING.md
│   ├── 05_REVIEW.md
│   └── code-walkthrough/      # Verbatim code + detailed guided explanation
├── scripts/start.sh
├── .gitignore
└── README.md
```

## Tests

```bash
php tests/run.php             # Pure PHP unit tests
python3 tests/http_test.py    # HTTP test using a temporary PHP server
find . -name '*.php' -print0 | xargs -0 -n1 php -l  # PHP syntax check
```

`tests/http_test.py` starts its own PHP development server on a temporary loopback port and shuts it down after testing, so no server needs to be started manually. Test results and limitations are in `docs/test-report.md`.

## Learning guide

1. `learning/00_START_HERE.md` — shortest explanation and 15/30/60-minute study plans.
2. `learning/01_ARCHITECTURE.md` — inheritance, class responsibilities, imports, and directory map.
3. `learning/03_EXECUTION_FLOW.md` — complete HTTP request/data lifecycle and sample state traces.
4. `learning/02_KEY_CONCEPTS.md` — POST, `$_POST`, `header()`, `FileConvertible`, JSON, validation, XSS, etc.
5. `learning/code-walkthrough/download.md`, then `src__Support__RequestValidator.md`, `src__Support__RandomGenerator.md`, `src__Support__Exporter.md` — full code with annotated reading guide.
6. `learning/04_DEBUGGING.md` and `learning/05_REVIEW.md` — diagnostics and detailed answers.

## GitHub publication / Recursion submission

These commands publish a **new** repository. None have been executed on your GitHub account.

```bash
cd /path/to/Downloadable-Lorem-Ipsum-2
find . -maxdepth 2 -type f | sort
# Inspect .gitignore and source files; no secrets or private records should be committed.
git init
git status
git add .
git commit -m "Implement downloadable mock-data generator"
git branch -M main
# First create an empty PUBLIC repository named Downloadable-Lorem-Ipsum-2 in GitHub's website.
git remote add origin https://github.com/YOUR_USERNAME/Downloadable-Lorem-Ipsum-2.git
git push -u origin main
```

If you already created a repository, **do not** run `git init`/`git remote add` blindly. Check `git status` and `git remote -v`, then commit and run `git push` to the existing remote. If `remote origin already exists`, inspect the URL first and use `git remote set-url origin URL` only if needed.

GitHub CLI alternative for a **new** repo after a successful commit and `gh auth login`:

```bash
gh repo create Downloadable-Lorem-Ipsum-2 --public --source=. --remote=origin --push
```

If an origin already exists, use normal `git push` instead. HTTPS authentication normally uses the Git Credential Manager / token, whereas SSH authentication uses a key registered to the account. Verify the files and README on your public GitHub page and paste its **repository URL** into the Recursion submission form. Local PHP development servers are not public hosting; GitHub publishes the **source** but does not run PHP server-side.

## Limits and implementation choices

- No genuine personal records or live businesses. No storage layer; each submission generates new fixtures.
- PHP is used because the task explicitly illustrates `download.php`, `$_POST` and `header()`; the top-right Recursion language picker in the screenshot does not describe a required Node.js backend.
- `count` and `format` are required for the user form because the validation subsection explicitly requires missing-parameter rejection. The illustrative code earlier in the lesson supplies fallbacks (`5`, `html`); this project intentionally resolves that inconsistency in favor of validation.
- Restaurant employee count means **total per chain**, distributed across the branches; see `docs/assumptions.md`.
- HTML output is rendered inline, as in the sample; the other three formats trigger file downloads.
- PHP built-in server is for local development, not production deployment.
