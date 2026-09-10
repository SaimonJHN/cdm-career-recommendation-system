# Portal setup, operation and verification

This is the maintained operations guide. Older setup and API documents describe earlier versions and are historical references.

## Deploy this update

1. Finish any exams running on the old browser-only implementation before deploying. Existing completed attempts and Registrar decisions are preserved. Old browser drafts cannot establish a trusted server start time; they are not automatically imported into a new timed session.
2. Back up the database and uploaded files. Run `php artisan portal:backup` after the new code is available, or use your existing database backup tool before deployment.
3. From `backend`, run `php artisan migrate --force`. Never use `migrate:fresh` on a database containing applicant records.
4. Configure the values below. Run `php artisan config:cache` only after settings are correct.
5. Run `npm install` if dependencies are missing, then `npm run build` in both `frontend` and `admin`. Deploy each `dist` directory with SPA fallback to `index.html`. Point their `VITE_API_URL` settings at the backend `/api` URL before building.
6. Schedule `php artisan schedule:run` every minute with the hosting scheduler. On Windows use Task Scheduler, set the working directory to `backend`, and run hidden/non-interactively. For local development, `php artisan schedule:work` is an alternative long-running process. Do not run both mechanisms on the same instance.
7. Run `php artisan portal:health`; also inspect Admin → System Health. The first scheduler heartbeat appears after maintenance runs.

## Configuration

| Setting | Purpose |
| --- | --- |
| `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` | Production error handling and backend URL. |
| `FRONTEND_URL` | Exact public student portal origin; password reset and notification links use it. |
| `SESSION_TOKEN_MINUTES=720` | Maximum bearer-token age, including existing tokens. Exam deadlines remain independent. |
| `CAMPUS_AUTH_PER_MINUTE=2000` | Shared-IP authentication ceiling. Per-email/challenge limits still apply. Tune using staging measurements. |
| `MAIL_*` | SMTP delivery settings. Configure a real sender; do not use log mail transport for production password resets. |
| `MYSQLDUMP_BINARY` | Path to `mysqldump` for MySQL database backups; omitted means executable on PATH. |
| `CACHE_DRIVER` | Use a shared supported cache when running multiple application instances so rate limits and locks are shared. |

The project already uses `FRONTEND_URL`; retain the correct existing value rather than copying a different development port from an old guide.

## Examination behavior

- Students have at most two submitted attempts. A second attempt is available only after a published first-attempt failure.
- Starting creates a server session with a fixed 120-minute deadline and a snapshot of all assigned questions, options and answer keys. The snapshot hash is the bank version for that attempt. Later question edits apply to new sessions only.
- Exactly 20 active questions per topic must be available. A fresh installation contains sample questions and needs a complete school-reviewed bank before exams can start. Do not fill a production bank with synthetic load-test questions.
- Authenticated exam instructions return question IDs/topics, not question text. Actual content is released when a session starts or resumes.
- Connected changes save to the server. Revision checks prevent a stale tab from overwriting another device. The browser also keeps an unsent local backup where storage permits.
- At expiry, the server grades the answers received before the deadline. The scheduler finalizes abandoned exams; a later session/status request also finalizes an expired session. Late answers are not accepted.
- Clearing browser storage, signing in again, or changing devices does not reset the deadline. Students must reconnect before expiry to send offline edits. Refresh after a tab conflict to use the authoritative saved draft.
- A completed session accepts repeat submissions idempotently; it does not create another result. Beginning a third attempt is blocked.

## Registrar workflow

- Filter Results by applicant name/number, admission year, latest attempt, and release status. Each row identifies the attempt and result ID.
- For routine approval: filter Pending, select rows or all matching results, and approve using system scores. Publish approved results to release them.
- For exceptional passing: filter eligible failed second attempts, select students, supply an internal reason, and publish as passed. Original scores and decisions remain in admin records; student result/guidance responses omit the exact underlying score evidence and internal reasons.
- To correct a published result: use Correct result, enter a reason and the official score, and confirm publication. This removes an active Registrar-pass override if present, while preserving its prior state in the audit log. Only the latest attempt can be corrected and no retake may be active. Corrections never increase the allowed number of attempts.
- Result version checks reject stale individual approvals/corrections. Reload and review the current result instead of retrying an old edit blindly.

## Spreadsheet import

Download a fresh template. It includes `applicant_number`, `result_id`, `result_version`, `score`, and optional `remarks`. Identity/version columns must remain unchanged. Templates select up to 1,000 latest unpublished results; process/publish one batch before downloading the next.

Upload CSV, XLSX, TSV, TXT or JSON to preview changes. Uploading alone does not change results. Fix all invalid, duplicate or stale rows, then confirm the preview. A preview expires after 15 minutes and belongs to the admin who uploaded it. If a result changes before confirmation, the entire commit rolls back. Published results require the correction workflow.

## Notifications and email

Publication and correction create a student portal notification immediately. Persistent outbox rows are delivered by `portal:maintain`, up to 100 per run, with five attempts and increasing retry delays. An email failure does not undo a published result. Super admins can inspect failures and requeue them in System Health.

Mail has at-least-once delivery semantics: an interrupted worker after SMTP accepted a message may cause a duplicate on retry. In-app notifications are the authoritative source. OTP and password-reset emails use synchronous delivery with a bounded SMTP timeout; result notifications use the outbox. Test real mail delivery separately using a designated test account.

## Passwords and sessions

Forgot password sends a 30-minute single-use link for eligible verified password accounts. Responses do not disclose whether an email is registered. Google accounts continue to use Google sign-in. Resetting revokes old bearer tokens, trusted devices and outstanding OTP challenges.

Profile → Sign out other devices revokes other sessions and all trusted-device approvals. The current session stays signed in. Expired logins prompt reauthentication; server-saved exams retain their original deadline.

## Backups, restore drill and monitoring

`portal:backup` creates a private SQLite snapshot or a MySQL consistent dump under `backend/storage/app/private/backups`, plus SHA-256 checksum. The scheduler runs it daily at 02:00 in application time. It does not upload backups, remove older backups, or back up public uploads; configure encrypted off-site copying, file backups and retention in your hosting environment. Keep private backups outside web-accessible storage.

Restore drill:

1. Copy a backup to an isolated recovery machine/database and verify its SHA-256 checksum against the companion file.
2. For SQLite, open the copied database and run `PRAGMA integrity_check`; point a separate app instance at that copy. For MySQL, create an empty recovery database and import the dump with the MySQL client. Never test restores over the live database.
3. Use staging mail settings; verify applicant counts, both attempts, session deadlines and Registrar audit history. Restore uploaded files to the isolated instance as well.
4. Record duration and results. A real recovery requires an agreed maintenance window, a final live backup, and explicitly verified target paths/database names before switching traffic.

Run `portal:health` from an external monitor and alert on a nonzero exit code. It checks database access, writable storage, a recent scheduler heartbeat and failed/old email backlog. System Health displays these items and the latest locally recorded backup. A local backup timestamp does not prove off-site retention or successful restoration.

## Verification

```text
# backend
php vendor/bin/phpunit --bootstrap vendor/autoload.php tests/Unit

# frontend
npm test
npm run test:browser
npm run build

# admin
npm run build
```

PHP integration tests use an in-memory SQLite database. They do not call external AI services or send live mail. Playwright uses installed Google Chrome, temporary local Vite servers and mocked APIs to check student and Registrar screens. It does not use real accounts. The configured Windows commands use `npm.cmd`; on other operating systems replace that executable with `npm` in `frontend/playwright.config.mjs`. Use a supported Node release for Vite 8 (the verification environment uses Node 24).

Also perform a staging walkthrough with real test integrations: sign in, start/save/reload on a second device, publish a first failure, retake, bulk Registrar pass, correct it, import a stale spreadsheet, and reset a designated test password. Check narrow/mobile layouts and actual mail delivery.

## Load testing

`php artisan portal:prepare-load --students=1000` creates a separate `storage/app/private/load-test/load-test.sqlite` database and disposable token file. It refuses to overwrite an existing test directory and never uses the applicant database. Keep the token file private. Archive the test directory when finished.

Run a separate backend process with `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE` pointing to that absolute load-test database, empty `DATABASE_URL`, and a separate cache prefix. For meaningful production sizing, repeat with a disposable database on staging infrastructure equivalent to production; a local PHP development server is not a production benchmark.

The provided local router makes that isolation explicit: from `backend`, run `php -S 127.0.0.1:8099 ../tools/load-server.php`. It forces the private test database, separate cache, array mail transport and disabled AI. Stop it when the test finishes; never publish this router on a public interface.

Set these process environment variables and run `node tools/load-test.mjs` from the repository root:

```text
LOAD_TEST_CONFIRMED=staging
LOAD_BASE_URL=http://127.0.0.1:8099/api
LOAD_TOKEN_FILE=<absolute path to private load-test/tokens.json>
LOAD_CONCURRENCY=25
LOAD_ROUNDS=5
LOAD_EXAM_MODE=true
LOAD_REPORT=<report path>
```

Read-only mode exercises profile/status requests. Exam mode starts and submits synthetic first attempts; use a fresh dataset for a repeated exam-mode run. Increase concurrency gradually and compare failures, p50 and p95 latency. Test real same-campus-IP login/OTP traffic separately: token-based exam workload does not benchmark mail delivery or Google verification.

No result email action or certificate-download endpoint is currently offered in the student interface. Notifications link to the portal; old documents mentioning those endpoints are historical.

## Exam randomization (September 10, 2026)
Each new attempt, including a retake, shuffles the five categories and randomly selects 20 active questions per category in shuffled order. Categories remain contiguous. The assigned order and answer key are saved in the session; resume and repeat start requests preserve them. Existing sessions are unchanged. Banks with exactly 20 active questions per category vary order only; larger banks also vary selection. Randomization can produce overlaps and does not guarantee unique exams.

