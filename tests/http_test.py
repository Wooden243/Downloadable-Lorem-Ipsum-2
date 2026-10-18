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
