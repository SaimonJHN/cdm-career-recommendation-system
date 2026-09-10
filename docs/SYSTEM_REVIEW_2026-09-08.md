# System review — 8 September 2026

This is a source-code review of the student portal, administration portal, API routes, exam/results processing, authentication services, recommendation services, database structure, and available tests. It is not a production security audit, browser accessibility audit, or concurrency/load test. No application behavior was changed during this review.

Validation performed: all 19 frontend exam tests passed; ProgramRecommendationTest and RecommendationFlowTest passed with 11 tests and 118 assertions. These are focused tests, not proof of whole-system or 1,000-user readiness.

## Existing capabilities worth keeping

- Student registration/login with email OTP, Google login, trusted devices, profiles and applicant numbers.
- Automatically marked examination, browser draft recovery, topic navigation, timer, and server-enforced two-submission limit.
- Registrar approval/publication, spreadsheet import, bulk publication, and bulk Registrar pass decisions with internal reasons and audit records.
- Separate student/admin portals with admin role checks and account suspension.
- Program guidance based on saved category evidence and interests, with optional AI explanations and fallback when AI is unavailable.
- Program/question administration and student/admin activity logs.

## Fix first

### 1. Store exam sessions, deadlines and drafts on the server — high

Evidence: `frontend/src/pages/Exam.vue` creates the deadline using the browser clock and stores answers/deadline in localStorage. `backend/app/Http/Controllers/ExamController.php` validates the submitted time_spent range but does not track when an exam actually started.

Implication: the two-submission cap is enforced, but the 120-minute duration is not authoritative on the server. Clearing browser storage, changing devices, or modifying browser state can lose progress or restart the apparent timer before submission.

Add an exam-attempt session with a server start/deadline, assigned questions, saved answers, and submission state. Restore from that session and apply a defined deadline policy even if the student stays offline. Preserve the existing one-retake rule.

### 2. Freeze the question set and answer key for each attempt — high

Evidence: ExamController fetches the currently active question bank again at submission; AdminManagementController allows question edits. Browser question signatures detect changes when restoring, but there is no server snapshot of question text/options/key assigned at start.

Implication: editing an answer key during an exam can change marking without changing question IDs. Changing the bank can also prevent submission or restoration.

Add versioned/published exam banks and bind each attempt to a version. Keep corrections separate from questions already assigned. Protect `/exam/questions`, which is currently a public route, and release questions through an authorized attempt. Do not introduce randomization unless the school wants to change the current fixed-order format.

### 3. Use consistent official outcomes across dashboards — high

Evidence: the admin dashboard counts and labels results using `is_passed`, which represents automatic marking. Registrar overrides deliberately preserve that field. The student dashboard uses only `exam_taken` and still says "Five Grade 12-level questions."

Implication: a student can officially pass while the admin overview still says "Not passed." Submission totals count attempts, not unique students.

Add a shared official-outcome calculation and clearly separate automatic performance from official decisions. Show unique applicants, attempts, pending results, available retakes and final outcomes. Populate question counts from the actual exam configuration.

### 4. Finish the privacy treatment for Registrar pass decisions — high

Evidence: ExamController hides the numerical score and remarks for Registrar passes, but ResultController returns ProgramMatcher evidence containing correct/total counts per category.

Implication: the internal decision reason is not exposed by this flow, but the original total can still be reconstructed from the recommendation evidence. Hiding the score in one page does not keep it private throughout the application.

If original scores must remain admin-only after an override, use an explicit student-facing recommendation response that excludes those counts and any other exact-score disclosure. Keep the full evidence for internal calculations/admin review. Test both result and recommendation endpoints.

### 5. Adjust login throttling for shared campus networks — high before mass use

Evidence: RouteServiceProvider limits password and Google login to five requests per minute per IP, registration to three, and OTP verification to ten.

Implication: many students behind one campus internet connection share these limits. Legitimate simultaneous sign-ins can receive rate-limit errors. This is a code-derived risk, not a measured load-test result.

Use layered account/challenge limits plus a suitable shared-IP limit, retaining abuse protection. Test a realistic same-IP exam-day login burst, OTP delivery and database behavior.

### 6. Show request failures as failures, not missing records — medium

Evidence: Results.vue catches a non-404 error into `message`, but leaves `completed` false. The template then selects "No completed exam yet" instead of showing that message.

Add separate loading/error/no-result states and a retry button. Also distinguish submission conflicts from confirmed completion: Exam.vue currently treats every 409 as a completed exam.

## Most useful additions

### 7. Student status timeline and notifications

Show: exam in progress → submitted → awaiting Registrar → published → retake available/final outcome. Notify students when results are released or a retake becomes available. Link directly to the next action. Current publishing methods update records without sending a result notification.

Use queued delivery and retry tracking for notifications. OTP delivery is currently synchronous; review its behavior under load separately so login codes remain timely.

### 8. Bulk approval using calculated scores

The system supports bulk import and bulk publication, but routine approval still involves individual prompts or exporting/importing a spreadsheet. Add selected/all-pending approval using stored system scores, with a preview and count. Keep this distinct from exceptional Registrar pass decisions.

### 9. Better result search, attempt history and corrections

Add name/applicant-number search to Results, explicit Attempt 1/Attempt 2 labels, latest-attempt filtering and admission-year filters. The results endpoint currently filters automatic pass status and release status but has no name search or explicit attempt column.

Add a controlled correction workflow with before/after audit history for published results and accidental Registrar pass decisions. Currently the UI hides approval for published results, while the new Registrar pass marker has no correction/reversal action. Any correction must preserve the two-attempt cap and be reflected consistently in student results.

### 10. Preview and identify spreadsheet updates precisely

Imports locate a student's latest result using applicant number and exam_date, update records immediately, and stop after 1,000 data rows. The downloadable template can include up to 10,000 students.

Add a validation preview showing changes/errors before committing, include result ID/attempt identity to prevent a stale spreadsheet from targeting a newer attempt, and make batch size limits consistent. Add deterministic ordering for attempts with equal timestamps. Preserve Registrar decision protections.

### 11. Password recovery and session management

There are password-based student accounts, but no password-reset routes in the current API. Add verified password recovery, revoke old sessions after a reset, and provide a student "sign out other devices" action.

Sanctum's configured expiration is null, and observed token creation does not supply an expiry. Define session lifetimes and expired-token handling appropriate to exams; an expired session should allow reauthentication without losing the server-saved attempt.

### 12. Operational readiness and broader tests

Add a documented backup and restoration procedure, deployment checks, application/queue health checks and alerts. No application backup schedule is configured in Console/Kernel.php; external hosting backups were not inspected and may already exist.

Expand automated coverage to login/OTP recovery, admin permissions, simultaneous submissions, spreadsheet preview/import, published-result corrections, recommendation privacy, and actual browser flows. Add a load test for the expected number of concurrent students; 1,000 registered students and 1,000 simultaneous test-takers are different capacity requirements.

Update deployment documentation and assistant help text: they currently mention result email/certificate features whose routes are not registered in the current API. Implement these only if needed, or remove the claims. Keep one maintained setup/deployment guide.

## Suggested implementation order

1. Resolve official-outcome/privacy inconsistencies and misleading error states.
2. Build server-managed exam sessions and versioned questions.
3. Fix shared-network login behavior; test realistic exam-day traffic.
4. Add status notifications, result search/attempt history and bulk routine approval.
5. Add correction/import safeguards, account recovery and operational checks.

Do not prioritize more AI features yet. The existing recommendation flow already separates calculated rankings from optional explanations. Reliability of exam evidence and Registrar workflows brings more immediate value.
