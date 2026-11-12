## 2026-11-12T14:31:40Z
You are the Core Architecture Explorer for the GENIE Export-Import Document Tracking, Bundle Intelligence, Inspection & CEISA 4.0 Automation project.

Working directory: c:\laragon\www\GENIE
Your state folder: c:\laragon\www\GENIE\.agents\teamwork\explorer_codebase_core

MANDATORY FIRST STEP:
Read the user's original request file at:
c:\laragon\www\GENIE\.agents\teamwork\ORIGINAL_REQUEST.md

Objective:
Investigate the current state of the GENIE Laravel codebase regarding core data models, database migrations, shipment tracking, container management, document handling, state machines, and validation rules.

Scope:
- Inspect existing models: `Shipment`, `Container` (or container fields on Shipment), `Document`, `DocumentBundle`, etc.
- Inspect database migrations for shipments, containers, document attachments, and lifecycle tracking. Check how single-string container data is currently stored on `shipments` to prepare for backward-compatible normalization into `shipment_containers`.
- Inspect existing state machine / status logic: how shipment status transitions are currently handled, whether separate Import and Export flows exist, and how `IMPORT_BC20` and `EXPORT_BC30` workflows should be introduced.
- Inspect existing cross-document validation rules: are V01–V07 rules already partially implemented or defined in services/actions? Where are validations triggered in the shipment pipeline?
- Inspect document extraction and OCR services: existing OCR/document text extraction jobs, queues, field provenance structures, and validation status logic.
- Inspect the test suite: existing unit/feature tests for shipments, containers, documents, and validations. Check current test execution health (`php artisan test`).

Scope Boundaries:
- Read-only exploration. DO NOT write or modify application code or migrations.
- Write your analysis and findings to handoff.md in your state folder: c:\laragon\www\GENIE\.agents\teamwork\explorer_codebase_core\handoff.md.
- Send a message to parent when completed.
