# tests/http_test.py — 全文・逐次解説

## ① ファイル概要

実際のPHP開発サーバーを子プロセスとして起動し、HTTP GET/POST/ダウンロード応答を調べる結合テスト。

**場所**：`tests/http_test.py`。
**入出力と他ファイルの関係**：一時ポートを選び urllib で実HTTP通信。実行終了後finallyでサーバーを停止する。

**前提知識**：外部Pythonパッケージ不要, socketで空きポートを選択, subprocessでPHP server起動。

## ② ソースコード全文（省略なし）

以下は納品ZIPの `tests/http_test.py` の全文で、生成時のSHA-256は `95839a04e6516c566822fb69d2f86e1967c1780294d1ffab7dcc6984cbe5264a`。

```python
"""HTTP integration tests: start real PHP built-in server and verify POST responses.
Requires Python 3 and php; no third-party modules.
"""
import json
import os
import socket
import subprocess
import sys
import time
from pathlib import Path
from urllib.parse import urlencode
from urllib.request import Request, urlopen
from urllib.error import HTTPError

ROOT = Path(__file__).resolve().parents[1]
with socket.socket() as sock:
    sock.bind(("127.0.0.1", 0))
    PORT = sock.getsockname()[1]
BASE = f"http://127.0.0.1:{PORT}"
server = subprocess.Popen(
    ["php", "-S", f"127.0.0.1:{PORT}", "-t", str(ROOT)],
    stdout=subprocess.DEVNULL,
    stderr=subprocess.PIPE,
)
passed = 0
failed = 0


def check(cond, desc):
    global passed, failed
    if cond:
        passed += 1
        print("PASS", desc)
    else:
        failed += 1
        print("FAIL", desc)


def request(path, data=None):
    encoded = None if data is None else urlencode(data, doseq=True).encode("utf-8")
    req = Request(BASE + path, data=encoded)
    try:
        with urlopen(req, timeout=5) as r:
            return r.status, r.headers, r.read()
    except HTTPError as error:
        return error.code, error.headers, error.read()


try:
    for _ in range(50):
        try:
            status, headers, body = request('/generate.php')
            break
        except Exception:
            if server.poll() is not None:
                raise RuntimeError('PHP server exited unexpectedly')
            time.sleep(.1)
    else:
        raise RuntimeError('PHP server unavailable')
    check(status == 200, 'GET /generate.php -> 200')
    check(b'method="post"' in body and b'action="/download.php"' in body, 'Forms submit to /download.php with POST')
    check(b'name="chain_count"' in body and b'name="postal_max"' in body, 'Chain extension controls rendered')
    status, headers, body = request('/download.php')
    check(status == 405 and headers.get('Allow') == 'POST', 'GET download.php -> 405 Allow POST')
    for fmt in ('json', 'md', 'txt', 'html'):
        status, headers, body = request('/download.php', {'dataset': 'users', 'count': '3', 'format': fmt})
        check(status == 200, f'POST users {fmt} -> 200')
        check(headers.get('Content-Type', '').lower().startswith({'json': 'application/json', 'md': 'text/markdown', 'txt': 'text/plain', 'html': 'text/html'}[fmt]), f'users {fmt}: correct Content-Type')
        check(('attachment' in headers.get('Content-Disposition', '')) == (fmt != 'html'), f'users {fmt}: Content-Disposition')
        if fmt == 'json':
            data = json.loads(body.decode('utf-8'))
            check(len(data) == 3 and all('name' in u for u in data), 'JSON user array has 3 records')
        if fmt == 'html':
            check(b'<!doctype html>' in body and b'Generated Users' in body, 'HTML preview full document')
    chain_form = {'dataset': 'chains', 'format': 'json', 'chain_count': '2', 'employee_count': '4',
                  'location_count': '3', 'salary_min': '45000', 'salary_max': '45000',
                  'postal_min': '5', 'postal_max': '5'}
    status, headers, body = request('/download.php', chain_form)
    check(status == 200, 'POST chain generation -> 200')
    chains = json.loads(body.decode('utf-8'))
    check(len(chains) == 2 and all(len(c['restaurantLocations']) == 3 for c in chains), '2 chains / 3 locations each')
    check(all(sum(len(loc['employees']) for loc in chain['restaurantLocations']) == 4 for chain in chains), '4 employees per chain')
    check(all(loc['postalCode'] == '00005' for chain in chains for loc in chain['restaurantLocations']), 'Postal code range respected')
    check(all(e['salary'] == 45000 for chain in chains for loc in chain['restaurantLocations'] for e in loc['employees']), 'Salary range respected')
    for format in ('html', 'md', 'txt'):
        f = dict(chain_form, format=format)
        status, headers, body = request('/download.php', f)
        check(status == 200 and len(body) > 50, f'Chain {format} succeeds')
    invalid = [
        ({'format': 'json'}, 'missing count'),
        ({'count': '5'}, 'missing format'),
        ({'count': '0', 'format': 'json'}, 'count 0'),
        ({'count': '101', 'format': 'json'}, 'count 101'),
        ({'count': '2.5', 'format': 'json'}, 'count decimal'),
        ({'count': '5', 'format': 'yaml'}, 'wrong format'),
        ({'count[]': '5', 'format': 'json'}, 'count is array'),
        ({'count': '5', 'format[]': 'json'}, 'format is array'),
        ({'dataset': 'chains', 'format': 'json'}, 'missing chain fields'),
        (dict(chain_form, salary_min='50000'), 'inverted salary'),
        (dict(chain_form, postal_max='4'), 'inverted postal'),
    ]
    for form, name in invalid:
        status, headers, body = request('/download.php', form)
        check(status == 412 and b'Invalid request' in body, f'Invalid {name} -> 412')
    status, headers, body = request('/assets/styles.css')
    check(status == 200 and b'.form-grid' in body, 'CSS is served')
    check(headers.get('Content-Type', '').startswith('text/css'), 'CSS content-type')
    status, headers, body = request('/download.php', {'count': '2', 'format': 'json'})
    check(status == 200 and len(json.loads(body)) == 2, 'Dataset backwards-compatible default users')
finally:
    server.terminate()
    try:
        server.wait(timeout=4)
    except subprocess.TimeoutExpired:
        server.kill()

print(f'\nHTTP SUMMARY: {passed} passed, {failed} failed')
sys.exit(1 if failed else 0)
```

## ③ 上から下へ — 逐次読解

**行番号は上記ソースコードの行番号に一致する。** 各行の構文・必要性・変更時の影響を確認する。

### 行 1〜10

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 1 | `"""HTTP integration tests: start real PHP built-in server and verify POST responses.` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 2 | `Requires Python 3 and php; no third-party modules.` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 3 | `"""` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 4 | `import json` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 5 | `import os` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 6 | `import socket` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 7 | `import subprocess` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 8 | `import sys` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 9 | `import time` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 10 | `from pathlib import Path` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 11〜20

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 11 | `from urllib.parse import urlencode` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 12 | `from urllib.request import Request, urlopen` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 13 | `from urllib.error import HTTPError` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 14 | `(空行)` | 空行。論理的な段落の境界。 |
| 15 | `ROOT = Path(__file__).resolve().parents[1]` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 16 | `with socket.socket() as sock:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 17 | `sock.bind(("127.0.0.1", 0))` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 18 | `PORT = sock.getsockname()[1]` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 19 | `BASE = f"http://127.0.0.1:{PORT}"` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 20 | `server = subprocess.Popen(` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 21〜30

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 21 | `["php", "-S", f"127.0.0.1:{PORT}", "-t", str(ROOT)],` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 22 | `stdout=subprocess.DEVNULL,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 23 | `stderr=subprocess.PIPE,` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 24 | `)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 25 | `passed = 0` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 26 | `failed = 0` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 27 | `(空行)` | 空行。論理的な段落の境界。 |
| 28 | `(空行)` | 空行。論理的な段落の境界。 |
| 29 | `def check(cond, desc):` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 30 | `global passed, failed` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 31〜40

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 31 | `if cond:` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 32 | `passed += 1` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 33 | `print("PASS", desc)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 34 | `else:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 35 | `failed += 1` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 36 | `print("FAIL", desc)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 37 | `(空行)` | 空行。論理的な段落の境界。 |
| 38 | `(空行)` | 空行。論理的な段落の境界。 |
| 39 | `def request(path, data=None):` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 40 | `encoded = None if data is None else urlencode(data, doseq=True).encode("utf-8")` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 41〜50

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 41 | `req = Request(BASE + path, data=encoded)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 42 | `try:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 43 | `with urlopen(req, timeout=5) as r:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 44 | `return r.status, r.headers, r.read()` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 45 | `except HTTPError as error:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 46 | `return error.code, error.headers, error.read()` | 関数の返り値。呼び出し元へデータを渡して、この関数の処理を終える。 |
| 47 | `(空行)` | 空行。論理的な段落の境界。 |
| 48 | `(空行)` | 空行。論理的な段落の境界。 |
| 49 | `try:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 50 | `for _ in range(50):` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 51〜60

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 51 | `try:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 52 | `status, headers, body = request('/generate.php')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 53 | `break` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 54 | `except Exception:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 55 | `if server.poll() is not None:` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 56 | `raise RuntimeError('PHP server exited unexpectedly')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 57 | `time.sleep(.1)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 58 | `else:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 59 | `raise RuntimeError('PHP server unavailable')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 60 | `check(status == 200, 'GET /generate.php -> 200')` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |

### 行 61〜70

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 61 | `check(b'method="post"' in body and b'action="/download.php"' in body, 'Forms submit to /download.php with P…` | フォーム送信方式をHTTP POSTに設定。GETとは本文/URLの扱いが変わる。 |
| 62 | `check(b'name="chain_count"' in body and b'name="postal_max"' in body, 'Chain extension controls rendered')` | フォームフィールド名を定義。サーバー側のPOST配列のキーに対応する。 |
| 63 | `status, headers, body = request('/download.php')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 64 | `check(status == 405 and headers.get('Allow') == 'POST', 'GET download.php -> 405 Allow POST')` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 65 | `for fmt in ('json', 'md', 'txt', 'html'):` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 66 | `status, headers, body = request('/download.php', {'dataset': 'users', 'count': '3', 'format': fmt})` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 67 | `check(status == 200, f'POST users {fmt} -> 200')` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 68 | `check(headers.get('Content-Type', '').lower().startswith({'json': 'application/json', 'md': 'text/markdown'…` | 出力のメディアタイプと文字コードをブラウザへ通知する。 |
| 69 | `check(('attachment' in headers.get('Content-Disposition', '')) == (fmt != 'html'), f'users {fmt}: Content-D…` | ブラウザにインライン表示か添付保存かを指示するヘッダー。 |
| 70 | `if fmt == 'json':` | 条件分岐。この条件を満たすときだけ処理を進める。 |

### 行 71〜80

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 71 | `data = json.loads(body.decode('utf-8'))` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 72 | `check(len(data) == 3 and all('name' in u for u in data), 'JSON user array has 3 records')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 73 | `if fmt == 'html':` | 条件分岐。この条件を満たすときだけ処理を進める。 |
| 74 | `check(b'<!doctype html>' in body and b'Generated Users' in body, 'HTML preview full document')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 75 | `chain_form = {'dataset': 'chains', 'format': 'json', 'chain_count': '2', 'employee_count': '4',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 76 | `'location_count': '3', 'salary_min': '45000', 'salary_max': '45000',` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 77 | `'postal_min': '5', 'postal_max': '5'}` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 78 | `status, headers, body = request('/download.php', chain_form)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 79 | `check(status == 200, 'POST chain generation -> 200')` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 80 | `chains = json.loads(body.decode('utf-8'))` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 81〜90

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 81 | `check(len(chains) == 2 and all(len(c['restaurantLocations']) == 3 for c in chains), '2 chains / 3 locations…` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 82 | `check(all(sum(len(loc['employees']) for loc in chain['restaurantLocations']) == 4 for chain in chains), '4 …` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 83 | `check(all(loc['postalCode'] == '00005' for chain in chains for loc in chain['restaurantLocations']), 'Posta…` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 84 | `check(all(e['salary'] == 45000 for chain in chains for loc in chain['restaurantLocations'] for e in loc['em…` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 85 | `for format in ('html', 'md', 'txt'):` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 86 | `f = dict(chain_form, format=format)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 87 | `status, headers, body = request('/download.php', f)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 88 | `check(status == 200 and len(body) > 50, f'Chain {format} succeeds')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 89 | `invalid = [` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 90 | `({'format': 'json'}, 'missing count'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 91〜100

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 91 | `({'count': '5'}, 'missing format'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 92 | `({'count': '0', 'format': 'json'}, 'count 0'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 93 | `({'count': '101', 'format': 'json'}, 'count 101'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 94 | `({'count': '2.5', 'format': 'json'}, 'count decimal'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 95 | `({'count': '5', 'format': 'yaml'}, 'wrong format'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 96 | `({'count[]': '5', 'format': 'json'}, 'count is array'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 97 | `({'count': '5', 'format[]': 'json'}, 'format is array'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 98 | `({'dataset': 'chains', 'format': 'json'}, 'missing chain fields'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 99 | `(dict(chain_form, salary_min='50000'), 'inverted salary'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 100 | `(dict(chain_form, postal_max='4'), 'inverted postal'),` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 101〜110

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 101 | `]` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 102 | `for form, name in invalid:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 103 | `status, headers, body = request('/download.php', form)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 104 | `check(status == 412 and b'Invalid request' in body, f'Invalid {name} -> 412')` | 既存オブジェクトのプロパティやメソッドにアクセスする。 |
| 105 | `status, headers, body = request('/assets/styles.css')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 106 | `check(status == 200 and b'.form-grid' in body, 'CSS is served')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 107 | `check(headers.get('Content-Type', '').startswith('text/css'), 'CSS content-type')` | 出力のメディアタイプと文字コードをブラウザへ通知する。 |
| 108 | `status, headers, body = request('/download.php', {'count': '2', 'format': 'json'})` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 109 | `check(status == 200 and len(json.loads(body)) == 2, 'Dataset backwards-compatible default users')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 110 | `finally:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

### 行 111〜118

| 行 | ソースの要点 | 解説 |
|---:|---|---|
| 111 | `server.terminate()` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 112 | `try:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 113 | `server.wait(timeout=4)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 114 | `except subprocess.TimeoutExpired:` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 115 | `server.kill()` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 116 | `(空行)` | 空行。論理的な段落の境界。 |
| 117 | `print(f'\nHTTP SUMMARY: {passed} passed, {failed} failed')` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |
| 118 | `sys.exit(1 if failed else 0)` | この値の代入・連結・宣言は前後の処理に必要。変数の型と後続の利用箇所を照合する。 |

## ④ 実行トレース

GET /download.phpは405、count=3&format=jsonは200/attachment、有効ChainもJSON展開して内部件数を検査。

## ⑤ 最重要部分の再確認

1. 外部Pythonパッケージ不要。
2. socketで空きポートを選択。
3. subprocessでPHP server起動。
4. urllibで標準のPOSTフォーム送信。
5. HTTPErrorもレスポンスとして捕捉。
6. ステータス/ヘッダー/本文を確認。
7. finallyでサーバー停止。

## 変更するとどうなる？

このファイルを変更する場合、呼び出し元と返り値の契約を維持すること。例えば引数の名前や型を変えたらフォーム・バリデータ・テストを同時に更新する。モデルにプロパティを追加したならtoArray/toHTML/toString/toMarkdownへの反映を検討する。出力ヘッダーやバリデーションを変更した場合は `tests/http_test.py` と `tests/run.php` を再実行して回帰を確認する。

