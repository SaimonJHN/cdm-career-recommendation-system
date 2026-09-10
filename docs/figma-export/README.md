# Editable Figma export

[Open the editable design](https://www.figma.com/design/KER289R4BRzoLebNQXf3AP?node-id=60-2)

Exported from the current Vue student and administration interfaces on 2026-09-11, using fictional preview data. The file contains 47 captured screens plus an editable guide, covering public, student, administration, and 390-pixel mobile layouts. The guide provides a clickable index and prototype starting points.

Captured screens contain editable text, images, and shapes. They are not a reusable component/variant library. Local Segoe UI and Georgia fonts may be needed when editing text.

Navigation and selected actions are linked. Authentication, scoring, timers, email delivery, imports, publishing, and AI processing are represented by sample states; they do not execute in Figma. This export is a snapshot and does not automatically sync with code changes.

One additional mobile exam-instructions capture is still processing at the Figma service. Its capture ID is `3e09c338-cf5a-4e61-871c-dbdb824a9c0d`. Desktop instructions and the mobile active exam are already included. Poll this same ID before attempting any recapture.

The `source` directory archives temporary capture fixtures and browser helpers. These files were removed from the application roots to keep mocked API adapters out of normal development pages. They are reference tooling, not production entry points; their original relative imports require the original app directory context.

See `manifest.json` for completed screen node IDs.
