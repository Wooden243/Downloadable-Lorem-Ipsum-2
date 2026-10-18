# Verification report — Downloadable Lorem Ipsum (2)

## Test environment

- Linux container with PHP CLI **8.4.24**, Python 3 and the standard-library PHP built-in web server.
- Tests executed against the project source under a local 127.0.0.1 port.
- No Composer packages, database, external APIs, personal information or credentials were used.

## Verified tests

| Verification | Tool / command | Observed result |
|---|---|---|
| PHP unit/model and conversion tests | `php tests/run.php` | **70 passed, 0 failed** |
| HTTP endpoints and form/file response tests | `python3 tests/http_test.py` | **40 passed, 0 failed** |
| PHP syntax of all PHP files | `find . -name '*.php' -print0 \| xargs -0 -n1 php -l` | No syntax errors |
| HTTP 412 invalid requests | included in HTTP tests | Tested invalid/missing count, invalid format, array injection, missing chain fields, inverted salary/ZIP ranges |
| HTTP 405 wrong method | included in HTTP tests | GET `/download.php` returns 405 and `Allow: POST` |
| Four output formats x two datasets | included in HTTP tests | HTML, JSON, TXT, MD work for both users and chains |
| Range controls and serialization | PHP tests | Employee totals, exact branch counts, bounded pay and ZIP range, zero padding, nested JSON |
| HTML escaping | PHP tests | Model input containing a script tag is escaped in HTML export |

## Security and correctness notes

Inputs are verified again by the PHP endpoint, regardless of browser `min`/`max` attributes. Only bounded digits are accepted for numeric fields. Output formats and dataset kinds are allowlisted. JSON is serialized via PHP's native JSON implementation. Display strings use HTML escaping in model output. Fixed filenames eliminate header injection via user-controlled filenames. No form values are persisted.

## Not tested / outside verification

- User's specific WSL2 PHP setup and GitHub/Recursion platform grading; not accessible from this runtime.
- Performance/load testing beyond normal examples, concurrent multiuser workloads, external reverse proxies or production server configuration.
- Exact screenshot pixel parity. The browser UI is implemented functionally, not matched pixel for pixel.
- Windows browsers other than the available local headless Chromium.

This is a record of tests actually run locally; it must not be confused with Recursion's automated evaluation.
