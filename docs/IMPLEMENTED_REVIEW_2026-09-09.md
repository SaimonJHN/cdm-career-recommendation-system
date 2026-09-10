# Review implementation record

Implemented from SYSTEM_REVIEW_2026-09-08.md:

1. Server-managed exam sessions, fixed deadlines, revision-checked answer saves, browser fallback drafts, cross-device recovery, idempotent submission, and scheduled expiry.
2. Immutable question/answer-key snapshots with bank hashes, protected content release and database-enforced question retention. Question editors can deactivate questions for future exams.
3. Shared official-outcome calculation, distinct applicant/attempt counts and consistent dashboard labels.
4. Student result and recommendation responses exclude internal Registrar reasons and exact score evidence for Registrar pass decisions.
5. Per-account/challenge authentication throttles with a configurable campus IP ceiling, plus same-IP regression tests.
6. Results retry/error states, explicit exam conflicts and expired-session prompts.
7. Student status timeline, in-app notifications, persistent result-email outbox, retries and operator visibility.
8. Bulk approval of selected/all-filtered pending results using calculated scores, separate from exceptional Registrar passing.
9. Result search, year/latest-attempt filters, attempt labels and audited, version-checked published-result corrections.
10. Non-mutating spreadsheet previews, explicit result identities/versions, atomic confirmation and consistent 1,000-row limits.
11. Verified password recovery, single-use reset tokens, session lifetimes and sign-out-other-devices controls.
12. Scheduler jobs, private checksummed database backup command, isolated SQLite restore test, system-health command/admin screen, load-test fixtures/harness and maintained operations documentation.

Additional verification uncovered and fixed a MySQL TIMESTAMP default incompatibility, SQLite JSON containment differences in question retention, the old Technical Aptitude editor label, stale authentication tests, and vulnerable npm dependencies. Both npm trees reported zero vulnerabilities after updates. Lockfiles are retained for reproducible installation; private test tokens, backups and browser artifacts are ignored by version control.

Validation:

- 22 backend tests, 201 assertions passed, including an isolated backup restore check.
- 9 exam frontend behavior tests passed.
- 5 Playwright browser tests passed using mocked student/admin API responses.
- Student and admin production builds passed with Vite 8.
- An isolated local workload processed 1,000 synthetic students / 4,000 requests with 25 concurrent clients and zero failures. See load-test-local-2026-09-09.json. This run checks the session workload on a local PHP development server, not production capacity or simultaneous OTP delivery.
- Both feature migrations and the session-question relationship migration were applied to the configured local MySQL database; existing result rows were preserved.

Deployment-dependent work:

- A hosting/OS scheduler must invoke Laravel's scheduler every minute for unattended expiry, notification delivery and daily backups. Defining jobs in code does not install a hosting scheduler.
- Real SMTP credentials/sender and the correct FRONTEND_URL are required. No live email was sent as part of verification.
- MySQL backups require a working mysqldump executable (MYSQLDUMP_BINARY). Off-site backup storage, retention, external alert delivery and a production restore drill belong to the hosting environment.
- Repeat load testing on production-equivalent staging and exercise real Google/SMTP integrations with designated test accounts before mass use.

OPERATIONS.md is the maintained setup and operating guide.
