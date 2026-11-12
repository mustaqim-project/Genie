# Specification Mining Report: GENIE Export-Import Tracking, Bundle Intelligence, Inspection & CEISA 4.0 Automation

**Date**: 2026-11-12  
**Author**: Blueprint Spec Miner (Gen 2 Replacement)  
**Workspace**: `c:\laragon\www\GENIE`  
**State Folder**: `c:\laragon\www\GENIE\.agents\teamwork\spec_miner_blueprint_2`  
**Authoritative References**:
- `c:\laragon\www\GENIE\.agents\teamwork\ORIGINAL_REQUEST.md`
- `c:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\618327cc-947d-4ecf-a7cc-65877e9dcc42\09-End-to-End-System-Analysis-and-Architecture.md`
- Codebase files in `c:\laragon\www\GENIE` (`app/Services/`, `app/Http/Controllers/`, `database/migrations/`, `config/`)

---

## 1. Features Discovered

| # | Category | Feature | Description | Inputs | Outputs | Error Behavior | Discovered Via |
|---|----------|---------|-------------|--------|---------|----------------|----------------|
| 1 | R1: Foundation | Multi-Container Normalization (`shipment_containers`) | Relational 1-to-many container tracking preserving legacy single-string column on `shipments`. Stores container number, size, type, seals, timestamps, and EIR file. | `shipment_id`, `container_number`, `container_size` (20FT/40FT/40HC/LCL), `container_type` (GP/RF/OT), `initial_seal_no`, `bc_reseal_no` | `shipment_containers` record; summary string format `NO CONT + N kontainer` | Rejects invalid container formats; logs duplicate container entries | Blueprint §3.2, ORIGINAL_REQUEST R1 |
| 2 | R1: Foundation | ISO 6346 Container Checksum Validation | Strict verification of standard ISO 6346 container numbering (4 uppercase prefix letters, 6 serial digits, 1 check digit) with modulo-11 checksum algorithm. | 11-character string (e.g. `WHLU9182341`) | Boolean pass/fail, calculated check digit vs actual | Fails with warning/mismatch if format fails regex `^[A-Z]{4}[0-9]{7}$` or checksum mismatch | Blueprint §3.2 & §3.3, `CrossDocumentValidationService.php:164` |
| 3 | R1: Workflow | Import State Machine (`IMPORT_BC20`) | 21-stage sequential lifecycle from customer instruction receipt (`IMP_01_DOC_RECEIVED`) to financial closing (`IMP_21_COMPLETED`), supporting branching on customs response (SPPB, NPD/DNP, SPJM). | Transition trigger events, verified document uploads, billing payments, customs response signals | Stage updates on `shipments.status_tahap`, audit log events in `shipment_events` | Throws validation exception if mandatory documents or approvals are missing for target stage | Blueprint §2.2, ORIGINAL_REQUEST R1 |
| 4 | R1: Workflow | Export State Machine (`EXPORT_BC30`) | 17-stage sequential lifecycle from shipping instruction receipt (`EXP_01_INSTRUCTION_RECEIVED`) to export invoicing (`EXP_17_COMPLETED`), handling export readiness, booking receipt, stuffing photos, and PPBE inspection. | SI, Booking Receipt, PEB draft approval, stuffing photo confirmation, NPE / PPBE response | Stage updates on `shipments.status_tahap`, export readiness parameters | Blocks progression if vessel closing time is exceeded or export permit missing | Blueprint §2.3, ORIGINAL_REQUEST R1 |
| 5 | R1: Validation | Cross-Document Validation Engine (Rules V01–V07) | Inter-document consistency checker comparing B/L consistency, weight ratios, invoice sums, container numbers, BTKI HS codes, foreign exchange currencies, and 16-digit NPWP. | Document field key-values from bundle (`import_fields`, `shipment_items`, `shipments`) | Structured validation results with status: `Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review` | Emits `BLOCKING` status (blocks CEISA submission) or `WARNING` status (notifies operator) | `CrossDocumentValidationService.php`, Blueprint §1.3 & §3.3 |
| 6 | R2: Security | MCP Bearer Token Auth & Granular Scope Enforcement | Mandatory token authentication on all `/api/mcp/*` endpoints; validates token hash against `mcp_credentials` and verifies granular scopes (`*:read`, `*:analyze`, `*:write`). | HTTP `Authorization: Bearer <token>`, query `?token=`, or header `X-MCP-Token` | Authorized MCP session; tool execution output | HTTP 401 if token missing/revoked/invalid; HTTP 403 if required tool scope is absent | Blueprint §3.6, `McpCustomsAgentController.php:68-85` |
| 7 | R2: Security | CEISA Endpoint Protection & RBAC Guard | Authentication and permission protection on H2H submission endpoints (`/api/v1/ceisa/bc-20/submit`, `/api/v1/ceisa/bc-30/submit`). | Authenticated Sanctum token, permission `ceisa.submit` | CEISA submission payload dispatch | HTTP 401 Unauthenticated; HTTP 403 Forbidden without `ceisa.submit` permission | Blueprint §3.6, `routes/api.php:6-15` |
| 8 | R2: Security | Immutable Human Approval Gate Token | Cryptographic lock mechanism requiring explicit supervisor sign-off before customs declaration submission to CEISA; prevents unauthorized automatic submission by AI or background jobs. | `approved_by_user_id`, raw payload SHA-256 hash at approval time | Approval token recorded in `ceisa_documents`; unlocks submission | Submission aborted with 403 if unapproved or payload hash changed post-approval | Blueprint §3.5, ORIGINAL_REQUEST R2 |
| 9 | R3: Intelligence | Field-Level Provenance & Non-Destructive Editing | Document intelligence extraction recording source traceability per field; preserves original OCR text while capturing human corrections. | Uploaded documents (PDF, JPG, PNG, XLSX, ZIP) | `import_fields` with `document_id`, `page_number`, `bounding_box` JSON, `extraction_engine`, `confidence_score`, `original_value`, `corrected_value` | Missing coordinates logged as null; uncorrected values default to `original_value` | Blueprint §3.2 & §3.3, `2026_11_12_000002_sprint_2_f15_f16_core_engine.php` |
| 10 | R3: Intelligence | Asynchronous Queue-Backed OCR Pipeline | Dual-track document parsing: native text layer extraction (Smalot PDF, PhpSpreadsheet) with fallback to queued OCR (Tesseract / Vision AI) and SHA-256 duplicate detection. | Multi-file document upload payload | Parsed text tokens, document classification (`INVOICE`, `PACKING_LIST`, `BL_AWB`, etc.), populated `import_fields` | Marks blurry / unparseable pages as `Requires Manual Review`; duplicate files flagged `DUPLICATE` | Blueprint §3.3, `DocumentExtractionService.php` |
| 11 | R3: Intelligence | HS Code Intelligence Workbench (2-Panel UI) | Split-view workbench providing side-by-side comparison between Final HS Code Form (left) and Top-3 BTKI / AI Recommendations with rationale & tariff snapshot (right). | Item description, 10 product attributes (material, function, specs, catalog) | Top-3 ranked HS codes, duty rates (BM, PPN, PPh, BK), Lartas requirements | Generic descriptions trigger `Manual Review - Clarify Product Specs` guardrail | Blueprint §3.4 & §4.2 |
| 12 | R3: Intelligence | Explicit Approve (`V`) / Reject (`X`) & Audit Logs | Mandatory human interaction buttons recording explicit acceptance or rejection of HS code recommendations into immutable audit trail (`hs_code_override_logs`). | Button click (`V` or `X`), override reason (if rejected), final selected HS code | Record created in `hs_code_override_logs` and `hs_code_recommendations.review_decision` | Blocks PIB/PEB finalization if any line item has unapproved / pending HS Code | Blueprint §3.4, ORIGINAL_REQUEST R3 |
| 13 | R3: Intelligence | Empirical HS Code Accuracy & Calibration Protocol | Framework separating confidence score from actual accuracy; requires benchmark evaluation on gold-standard dataset of 500-1000 verified goods lines. | Ground truth dataset verified by certified PPJK / DJBC decisions | Top-1 Accuracy (target >=90%), Top-3 Accuracy (target >=96%), Precision/Recall per chapter, ECE calibration | Identifies high-risk chapters (e.g. Ch. 84 vs 85 vs 39) requiring mandatory manual review | Blueprint §3.4 |
| 14 | R3: Intelligence | MCP Tool: `get_document_list` | Read tool retrieving document bundle checklist, upload status, mandatory vs conditional requirements, and missing documents for a shipment. | `shipment_id` (string/UUID) | JSON array of documents with verification status, file metadata, and missing checklist | Returns 404 if shipment not found; 403 if missing `shipments:read` scope | Blueprint §3.6, ORIGINAL_REQUEST R3 |
| 15 | R3: Intelligence | MCP Tool: `get_inspection_history` | Read tool retrieving customs inspection records, SPJM tickets, scheduled trattings, old vs new seal numbers, and photo evidence metadata. | `shipment_id` (string/UUID) | JSON inspection history array with stages, seals, TPS location, and photo URLs | Returns empty array if no inspections logged; 403 if missing scope | Blueprint §3.6, ORIGINAL_REQUEST R3 |
| 16 | R3: Intelligence | MCP Tool: `validate_pib_data` | Analyze tool executing full cross-document and schema validation ruleset for Import BC 2.0 (PIB) data bundle. | `shipment_id` or raw PIB payload | Structured validation report with 5-status categorization and CEISA schema pre-check summary | Returns blocking errors list; 403 if missing `shipments:analyze` scope | Blueprint §3.6, ORIGINAL_REQUEST R3 |
| 17 | R3: Intelligence | MCP Tool: `validate_peb_data` | Analyze tool executing full cross-document and schema validation ruleset for Export BC 3.0 (PEB) data bundle. | `shipment_id` or raw PEB payload | Structured validation report, export readiness check (`kesiapanBarang`, `bankDevisa`), BK tariff check | Returns blocking errors list; 403 if missing `shipments:analyze` scope | Blueprint §3.6, ORIGINAL_REQUEST R3 |
| 18 | R3: Intelligence | MCP Tool: `compare_hs_code` | Analyze tool comparing candidate 8-digit BTKI codes against item description, duty calculations, Lartas restrictions, and historical clearance precedent. | `item_description`, candidate HS codes array, country of origin | Comparative matrix with BM/PPN/PPh/BK rates, Lartas docs, and match rationale | Returns empty recommendations if no matching chapters found | Blueprint §3.6, ORIGINAL_REQUEST R3 |
| 19 | R3: Intelligence | MCP Tool: `generate_compliance_report` | Analyze tool synthesizing complete shipment compliance posture: document readiness, SLA countdown to demurrage, inspection risks, and tariff accuracy. | `shipment_id` (string/UUID) | Executive compliance summary, risk score, days left free-time, blocking items list | Returns 404 if shipment not found; 403 if missing scope | Blueprint §3.6, ORIGINAL_REQUEST R3 |
| 20 | R4: Operations | Customs Inspection Management (`customs_inspections`) | Structured tracking of customs physical inspections (SPJM behandle, NPD/DNP, PPBE) linking TPS location, inspector name, ticket number, and official inspection report (Berita Acara). | `shipment_id`, `container_id`, `inspection_type`, `ticket_number`, `scheduled_at`, `location_tps`, `inspector_name` | `customs_inspections` record with lifecycle state | Prevents clearance completion until inspection report (BA) is attached | Blueprint §3.2 & §4.2, ORIGINAL_REQUEST R4 |
| 21 | R4: Operations | Multi-Stage Photo Evidence & Seal Comparison | Timestamped and GPS-tagged photo capture across 5 import inspection stages or 4 export stuffing stages; tracks old seal vs new Bea Cukai seal. | Uploaded images, stage identifier, `seal_number`, GPS coordinates, `watermarked_at` | `inspection_photos` records linked to `customs_inspections` and `shipments` | Rejects photo if stage invalid; alerts if new seal number is identical to old broken seal | Blueprint §2.2, §2.3, §3.2, `2026_11_12_100000_add_tracking_dok_and_inspection_photos_tables.php` |
| 22 | R4: Operations | Delivery Tracking & Proof of Delivery (`shipment_deliveries`) | Tracking of TPS gate-out, trucking vendor assignment, driver credentials, armada plate number, arrival confirmation, and signed POD upload. | `shipment_id`, `container_id`, `trucking_vendor_name`, `driver_name`, `plate_number`, `gate_out_at`, `arrived_at`, signed POD file | `shipment_deliveries` record, automated stage advance to `DELIVERED_POD` | Prevents closing shipment without signed POD attachment | Blueprint §3.2 & §4.2, ORIGINAL_REQUEST R4 |
| 23 | R4: Operations | Port & Handling Operational Cost Reconciliation | Detailed recording of operational disbursement items (`DO`, `Extend D/O I-III`, `Refund`, `BHDL`, `STG`, `TKC`, `LOLO`, `Trucking`) reconciled against invoice numbers and Odoo. | `shipment_id`, `cost_category`, `vendor_name`, `amount`, `invoice_number`, `status` | `shipment_operation_costs` records, profit margin calculation per shipment | Alerts if pending costs exist when generating final invoice | Blueprint §1.1, §3.2, `2026_11_12_100000_add_tracking_dok_and_inspection_photos_tables.php` |
| 24 | R4: Operations | Resilient CEISA 4.0 API Client & Queue Backoff | Queue-based asynchronous worker for CEISA declaration submission and status polling featuring exponential retry backoff, rate limiting, and mock simulator. | Outbound JSON payload, `nomor_aju`, endpoint URL | Dispatched API call, HTTP response log in `ceisa_api_logs`, auto-retry on 429/502/503/504 | Retries up to 3 times (10s, 30s, 120s backoff); halts on permanent 4xx validation errors | Blueprint §3.5, `CeisaClientService.php`, `CeisaDocumentService.php` |
| 25 | R4: Operations | CEISA Webhook Signature Verification | Cryptographic verification of inbound event notifications from DJBC CEISA 4.0 gateway to prevent spoofing of customs responses. | Inbound HTTP request headers, webhook signature/token, raw payload | Verified webhook execution, status update on `ceisa_documents` and `shipments` | Rejects unverified requests with HTTP 401/403; logs raw attempt in `ceisa_api_logs` | Blueprint §3.5, `routes/api.php:7` |

---

## 2. Edge Cases

| # | Feature | Input | Observed Behavior |
|---|---------|-------|-------------------|
| 1 | ISO 6346 Container Checksum | Valid format string `TGHU1234567` where check digit arithmetic fails modulo-11 | Current `ruleV04ContainerChecksum` in `CrossDocumentValidationService.php:178` only checks regex `/^[A-Z]{4}[0-9]{7}$/` and returns `PASSED` without performing modulo-11 check. Full ISO 6346 checksum calculation must be enforced. |
| 2 | ISO 6346 Check Digit Modulo Remainder 10 | Container number whose weighted sum modulo 11 equals 10 | Check digit becomes `0` under ISO 6346 rules (or marks container number as invalid if standard strictness prohibits remainder 10). System must handle remainder 10 deterministically. |
| 3 | Multi-Container Backward Compatibility | Legacy shipment with free-text container string: `WHLU9182341 / TGHU8921342 (2x40HC)` | Reading legacy shipments without `shipment_containers` rows fails if child relationship is assumed non-empty. Normalizer must fallback to `shipments.nomor_kontainer` while creating synchronized child records during migration. |
| 4 | State Machine Transition Out of Order | Operator attempts to jump from `IMP_02_BUNDLE_UPLOADED` directly to `IMP_09_CEISA_SUBMITTED` | Stage prerequisite check in `StageJumpService` / Workflow engine must intercept and block transition because mandatory prerequisites (`IMP_05_CROSS_VALIDATED`, `IMP_06_HS_IDENTIFIED`, `IMP_08_PIB_VALIDATED`) are incomplete. |
| 5 | Customs Response Branching (Green vs Red Lane) | CEISA returns `SPPB` (Green Lane) for shipment in `IMP_11_BILLING_PAID` | State machine skips stages `IMP_14_STRIPPING_UNLOAD` through `IMP_16_PHOTO_RESEAL` and transitions directly to `IMP_17_CUSTOMS_CLEARED` -> `IMP_18_PORT_GATE_OUT`. |
| 6 | Customs Red Lane (SPJM) Resealing | Inspector completes physical exam; user enters same seal number in `new_seal_number` as `old_seal_number` | System must flag validation warning or block upload: Bea Cukai resealing requires a new, distinct customs bullet seal number (`new_seal_number != old_seal_number`). |
| 7 | Cross-Document Weight Inversion (V02) | Packing List net weight = 12,500.00 kg, gross weight = 11,200.00 kg | Rule V02 triggers `FAILED` warning: net weight exceeds gross weight. System flags `WARNING` and prevents draft finalization without explanation. |
| 8 | Invoice Header vs Item Accumulation Mismatch (V03) | Invoice header total = $50,000.00; line items sum = $49,998.50 (difference $1.50) | Rule V03 exceeds rounding threshold ($1.00 tolerance) and issues `FAILED` warning, categorizing status as `Mismatch`. |
| 9 | NPWP Format PMK 112/2022 (V07) | Importer NPWP submitted as 15-digit legacy number vs 16-digit NIK/NPWP | Rule V07 accepts both 15 digits (legacy corporate) and 16 digits (PMK 112/2022 NIK format). Rejects any string with count < 15 or > 16 digits. |
| 10 | Missing MCP Token in External Request | External HTTP request sent to `/api/mcp/execute` without `Authorization` header | `McpCustomsAgentController.php:68-70` currently returns `null`, and line 198 bypasses scope check, executing tool unauthenticated. Fixed behavior must immediately reject with HTTP 401 Unauthorized. |
| 11 | Revoked MCP Token | External request sends token with valid format whose `revoked_at` is non-null | `McpCredential::isValid()` evaluates to false; controller returns HTTP 401: `Unauthorized: Token MCP tidak valid atau telah dicabut.` |
| 12 | Insufficient Scope on Write Tool | MCP token with only `shipments:read` scope calls `shipment_update_status` | `getRequiredScopesForTool()` requires `shipments:write`; controller blocks execution with HTTP 403 Forbidden. |
| 13 | CEISA Submit Without Approval Gate Token | External API or operator triggers `POST /api/v1/ceisa/bc-20/submit` when `ceisa_documents.approved_by_user_id` is null | `CeisaDocumentService::submit()` must abort submission with HTTP 403: `Dokumen belum disetujui oleh Supervisor PPJK (Approval Gate Lock active)`. |
| 14 | Payload Tampered After Approval | Approval granted at timestamp T1; an item price is modified at T2 without re-approval | Hash verification `hash('sha256', json_encode($currentPayload)) !== $document->approval_payload_hash` fails; submission is blocked until supervisor re-approves modified payload. |
| 15 | HS Code Generic Commodity Input | Item description is `"Spare parts"` or `"Accessories"` with no catalog/material | Anti-hallucination guardrail blocks AI from predicting HS code with high confidence; sets status to `Requires Manual Review — Clarifikasi Spesifikasi Produk`. |
| 16 | HS Code Override with Blank Reason | Supervisor clicks `X` (Reject) on AI recommendation and types manual HS code, but leaves reason field blank | System enforces reason entry for audit trail (`hs_code_override_logs.override_reason` is required when rejecting Top-1 recommendation). |
| 17 | CEISA Gateway Transient Failure (HTTP 502 / 503 / 504 / 429) | CEISA DJBC gateway returns gateway timeout or rate limit exceeded during submit | `CeisaClientService` captures 502/503/504/429, logs failure in `ceisa_api_logs`, and dispatches retry job to queue with exponential backoff (10s -> 30s -> 120s), avoiding duplicate submission via `nomorAju` idempotency key. |
| 18 | CEISA Mock Mode Active | `config('ceisa.api.mock_mode') === true` | `CeisaClientService::generateMockResponse` returns deterministic mock response (`SPPB` for BC 2.0, `NPE` for BC 3.0), logs request as mock in `ceisa_api_logs`, and prevents real external HTTP transmission. |
| 19 | Corrupt or Scanned PDF Without Text Layer | Operator uploads low-resolution scanned JPG/PDF of Bill of Lading | Native parser extracts 0 bytes; document is dispatched to background queue OCR worker (Tesseract). If OCR confidence is < 0.80, field provenance records status `Requires Manual Review`. |
| 20 | Non-Destructive Edit Rollback | Operator corrects an OCR extraction value, then requests restore of original raw value | System maintains `original_value` immutable; clears `corrected_value` and resets status to `DETECTED`. |

---

## 3. Five-Component Handoff Report

### 3.1 Observation
1. **Spreadsheet Reality & Operational Columns**:
   - `06-Analisis-Sheet-Tracking-Dok.md` and Blueprint §1.1 document `5,425` total historical rows with `936` active shipment rows across **78 columns (`A–BZ`)** divided into 8 operational team zones.
   - `shipments` table currently stores container data in a single string column: `nomor_kontainer VARCHAR(100)` (`2026_10_12_000003_create_shipment_workflow_tables.php:69`).
2. **State Machine Disconnection**:
   - `ShipmentWorkflowService.php` lines 67-73 and `app/Models/Shipment.php:54` use a single column `status_tahap` for both Import and Export, sharing linear stages (`DOC_COLLECTING` to `DELIVERED_POD` / `INVOICED_P19`).
   - Blueprint §2.2 & §2.3 specifies a **21-stage state machine for Import (`IMPORT_BC20`)** and a **17-stage state machine for Export (`EXPORT_BC30`)**.
3. **Cross-Document Validation Engine Isolation**:
   - `app/Services/Shipment/CrossDocumentValidationService.php` contains complete rules V01 through V07:
     - `ruleV01BlConsistency` (lines 62-106): checks B/L consistency across batch.
     - `ruleV02WeightTolerance` (lines 108-132): checks net <= gross weight.
     - `ruleV03InvoiceItemsSum` (lines 134-162): compares invoice total to line items sum ($1 tolerance).
     - `ruleV04ContainerChecksum` (lines 164-197): checks ISO 6346 regex pattern.
     - `ruleV05HsCodeBtki` (lines 199-230): verifies 8-digit numeric pos tarif.
     - `ruleV06CurrencyAndKmk` (lines 232-256): verifies valid currency code for KMK customs exchange rate.
     - `ruleV07Npwp16Digit` (lines 258-281): validates 15 or 16 digit NPWP per PMK 112/2022.
   - Observation: This service currently only operates on `App\Models\ImportDocument` and writes to `import_validations` table (`CrossDocumentValidationService.php:14, 44-57`). The primary `ShipmentWorkflowService` and `ShipmentController` pipeline do not invoke V01–V07 during standard shipment lifecycle.
4. **Critical Security Vulnerabilities in Codebase**:
   - `app/Http/Controllers/Api/McpCustomsAgentController.php:68-70`:
     ```php
     if (!$rawToken) {
         return null; // Session internal atau unauthenticated call
     }
     ```
     At line 198:
     ```php
     if ($auth instanceof \App\Models\McpCredential) {
         // checks scopes
     }
     ```
     When `$rawToken` is missing, `$auth` is `null`. The condition evaluates to false, completely bypassing authorization and executing any tool requested!
   - `routes/api.php:6-15`:
     ```php
     Route::prefix('v1/ceisa')->group(function () {
         Route::post('/webhook', [CeisaApiController::class, 'handleWebhook'])->name('api.ceisa.webhook');
         Route::post('/bc-20/submit', [CeisaApiController::class, 'submitBC20']);
         Route::post('/bc-30/submit', [CeisaApiController::class, 'submitBC30']);
         ...
     });
     ```
     The submission routes have **zero middleware** attached (`auth:sanctum` missing). Any unauthenticated HTTP client can invoke `submitBC20` and `submitBC30`.
   - `app/Services/Ceisa/CeisaDocumentService.php:52-94`: `submit()` method checks `read_only` config, but has no verification of user approval or approval tokens.
5. **Document Intelligence & HS Code Gap**:
   - `app/Models/ShipmentItem.php` has `hs_confidence_score` defaulting to `0.95`, but no link to Top-3 recommendations or reason logs.
   - Tables `hs_code_recommendations` and `hs_code_override_logs` do not exist yet in database migrations.
6. **Field Operations & Delivery**:
   - `2026_11_12_100000_add_tracking_dok_and_inspection_photos_tables.php` added `inspection_photos` and `shipment_operation_costs`, but `customs_inspections` and `shipment_deliveries` tables do not exist yet.
   - Six required MCP tools (`get_document_list`, `get_inspection_history`, `validate_pib_data`, `validate_peb_data`, `compare_hs_code`, `generate_compliance_report`) are absent from `app/Domain/Mcp/Tools/McpToolRegistry.php`.

### 3.2 Logic Chain
1. Multi-container shipments frequently encounter distinct container numbers and seals (especially during customs red lane inspections where individual containers receive distinct BC bullet seals). Storing these in `shipments.nomor_kontainer VARCHAR(100)` causes data truncation, seal overwrite, and prevents individual gate-out tracking. Therefore, a dedicated `shipment_containers` table is structurally required while maintaining accessor compatibility on `Shipment`.
2. Import (`BC 2.0`) and Export (`BC 3.0`) follow fundamentally divergent regulatory paths:
   - Import involves manifest arrival, NDPBM tax exchange rate, billing payment via MPN G3, SPJM physical inspection / behandle, container resealing, DO redemption, and warehouse gate-out.
   - Export involves shipping instruction, export permit (Lartas/V-Legal/PE/ET), booking delivery order, container stuffing photo chronology, NPE release or PPBE physical exam, CY gate-in, and vessel departure (ETD/ATD).
   - Sharing a linear 11-stage pipeline causes stage pollution and unvalidated jumps. Therefore, distinct state machines (`IMPORT_BC20` with 21 states and `EXPORT_BC30` with 17 states) must govern workflow progression.
3. Cross-document validation rules V01–V07 are already well-written in `CrossDocumentValidationService.php`, but are trapped in the F15 batch import module. Bridging this service to evaluate `Shipment` entities directly and emitting structured 5-status validation results (`Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review`) will fulfill R1 without reinventing the rules.
4. Leaving `/api/mcp/*` open without mandatory Bearer authentication creates an immediate remote code / data modification exploit. Enforcing `$rawToken` presence and scope verification closes this gap. Similarly, protecting `/api/v1/ceisa/*` submission endpoints with authentication, permission checks, and an immutable approval token prevents accidental or malicious customs filings.
5. High model confidence scores (e.g. 0.95) frequently hallucinate when confronted with generic descriptions ("Spare parts", "Fittings"). Decoupling confidence score from actual empirical accuracy and enforcing a 2-panel human review workbench with explicit Approve (`V`) / Reject (`X`) decisions recorded in `hs_code_override_logs` guarantees compliance with DJBC regulations and establishes a validated dataset for continuous model calibration.
6. Field operations (SPJM inspection photos, seal replacement tracking, and delivery POD) require relational integrity. Linking `inspection_photos` to `customs_inspections` and creating `shipment_deliveries` allows field staff to document old vs new seals and capture signed PODs, directly feeding into port cost reconciliation (`shipment_operation_costs`).

### 3.3 Caveats
- No application code or database migrations were modified during this analysis turn; this report is strictly read-only specification mining.
- CEISA 4.0 production credentials were not exercised live; analysis of CEISA behaviors relies on `config/ceisa.php`, `CeisaClientService.php`, official OpenAPI v2 DJBC specifications, and verified mock mode simulations.
- Ground truth dataset for empirical >=90% HS code evaluation is pending compilation by certified PPJK customs specialists.

### 3.4 Conclusion
The GENIE Export-Import Tracking and Automation project has strong foundational components (cross-document rules V01–V07, CEISA OpenAPI client, MCP tool framework, batch extraction tables), but suffers from four critical structural disconnects:
1. Multi-container and workflow state machine collapse (single string container and shared stage column).
2. Critical security openings in MCP authentication and CEISA submission routes.
3. Disconnection of cross-document validation from the primary shipment pipeline and absence of the 2-panel HS Code approval workbench.
4. Missing structured relational models for customs inspections, container-level delivery tracking, and queue retry backoff on CEISA communications.
Resolving these via the Phase 1–4 specifications documented herein will produce a production-grade, secure, and compliant customs operating system.

### 3.5 Verification Method
1. **Automated Test Suite**:
   Run `php artisan test` to establish the baseline and verify that existing test suites pass without regression.
2. **Security Verification**:
   - Issue an unauthenticated `GET /api/mcp/tools` and `POST /api/mcp/execute` request: verify response is HTTP 401 Unauthorized.
   - Issue an unauthenticated `POST /api/v1/ceisa/bc-20/submit`: verify response is HTTP 401 Unauthorized.
3. **Database Schema Inspection**:
   Inspect migration files and run `php artisan migrate:status` to ensure tables `shipment_containers`, `hs_code_recommendations`, `hs_code_override_logs`, `customs_inspections`, and `shipment_deliveries` conform to field specifications.
4. **CEISA Mock Mode Verification**:
   With `config('ceisa.api.mock_mode') === true`, verify declaration pre-check (`/document/check`) and submission flow returns valid DJBC v2 JSON mock responses and logs entries in `ceisa_api_logs`.

---

## 4. Comprehensive Technical Specifications by Domain

### 4.1 Foundation, Tracking & Multi-Container Normalization

#### Database Schema: `shipment_containers`
```sql
CREATE TABLE shipment_containers (
    id CHAR(36) PRIMARY KEY, -- UUID
    shipment_id CHAR(36) NOT NULL,
    container_number VARCHAR(20) NOT NULL, -- e.g. WHLU9182341
    container_size ENUM('20FT', '40FT', '40HC', '45FT', 'LCL') NOT NULL DEFAULT '40HC',
    container_type ENUM('GP', 'RF', 'OT', 'FR', 'TK') NOT NULL DEFAULT 'GP',
    initial_seal_no VARCHAR(60) NULL, -- Shipping line seal
    bc_reseal_no VARCHAR(60) NULL,    -- Customs bullet seal after inspection
    gate_out_at DATETIME NULL,
    delivered_at DATETIME NULL,
    eir_file_path VARCHAR(255) NULL,   -- Equipment Interchange Receipt
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    INDEX idx_shipment_container (shipment_id, container_number)
);
```

#### Backward Compatibility Strategy
- Existing column `shipments.nomor_kontainer VARCHAR(100)` remains intact.
- An Eloquent accessor `getNomorKontainerAttribute()` on `Shipment` summarizes child records:
  - If 1 container: returns `WHLU9182341 (40HC)`
  - If multiple containers: returns `WHLU9182341 (+2 kontainer)`
- When creating or updating a shipment with `total_kontainer > 1`, individual container rows are persisted in `shipment_containers`.

#### ISO 6346 Checksum Validation Algorithm
1. **Format Check**: Must match regular expression `/^[A-Z]{4}[0-9]{7}$/`.
   - Characters 1–3: Owner code (three uppercase letters).
   - Character 4: Equipment category identifier (`U` for freight containers, `J` for detachable equipment, `Z` for trailers and chassis).
   - Characters 5–10: Serial number (6 digits).
   - Character 11: Check digit (1 digit).
2. **Numeric Equivalence Mapping**:
   Letter to value mapping:
   - A=10, B=12, C=13, D=14, E=15, F=16, G=17, H=18, I=19, J=20
   - K=21, L=23, M=24, N=25, O=26, P=27, Q=28, R=29, S=30, T=31
   - U=32, V=34, W=35, X=36, Y=37, Z=38
   *(Note: Multiples of 11 — 11, 22, 33 — are omitted in the ISO 6346 standard).*
3. **Weighting Factor & Modulo 11**:
   For character index $i$ from 0 to 9:
   $$\text{Sum} = \sum_{i=0}^{9} \text{Value}(c_i) \times 2^i$$
   $$\text{Calculated Check Digit} = (\text{Sum} \pmod{11}) \pmod{10}$$
4. **Comparison**:
   Compare `Calculated Check Digit` with the 11th character of the container string. If equal, check passes; otherwise, validation fails.

---

### 4.2 Separate Import & Export State Machines

#### Import Workflow (`IMPORT_BC20`) — 21 Sequential Stages
1. `IMP_01_DOC_RECEIVED`: Customer instruction and preliminary files received. Trigger: Job creation. PIC: CS/Marketing.
2. `IMP_02_BUNDLE_UPLOADED`: Document bundle uploaded (Invoice, PL, BL, Insurance, COO). Trigger: File upload.
3. `IMP_03_CLASSIFIED`: Automatic and manual classification of document types. Trigger: Document classifier worker.
4. `IMP_04_EXTRACTED`: OCR extraction of header, containers, and line items. Trigger: Queue extractor.
5. `IMP_05_CROSS_VALIDATED`: Cross-document validation engine execution (Rules V01–V07). Trigger: Extraction completion.
6. `IMP_06_HS_IDENTIFIED`: HS code identification and Lartas verification. Trigger: HS Workbench review.
7. `IMP_07_DRAFT_PIB`: Preparation of Draft BC 2.0 and NDPBM tax exchange rate confirmation. Trigger: Operator draft action.
8. `IMP_08_PIB_VALIDATED`: Pre-check schema validation against CEISA `/document/check`. Trigger: Pre-check submission.
9. `IMP_09_CEISA_SUBMITTED`: Explicit supervisor approval and dispatch via `POST /document`. Trigger: User Approval Gate.
10. `IMP_10_CEISA_MONITORED`: Customs acknowledgment, registration number (NOPEN), and channel assignment monitoring. Trigger: Status polling / webhook.
11. `IMP_11_BILLING_PAID`: Payment of import duty, VAT, and income tax via MPN G3. Trigger: Billing receipt verification.
12. `IMP_12_CHANNEL_ROUTED`: Branching point based on CEISA channel:
    - **Green Lane** (`SPPB`): Advances directly to `IMP_17_CUSTOMS_CLEARED`.
    - **Yellow Lane** (`NPD`/`DNP`): Advances to `IMP_13_DOC_EXAM`.
    - **Red Lane** (`SPJM`): Advances to `IMP_14_STRIPPING_UNLOAD`.
13. `IMP_13_DOC_EXAM`: Document examination response and supplementary file upload to SLIM.
14. `IMP_14_STRIPPING_UNLOAD`: Coordination of container behandle, stripping, or TPS physical staging.
15. `IMP_15_PHOTO_INSPECTION`: Photo evidence capture (Before Unload, During Unloading, Goods Detail & Quantities).
16. `IMP_16_PHOTO_RESEAL`: Application of new customs seal (`new_seal_number`), photo of closed doors, and inspection report (BA).
17. `IMP_17_CUSTOMS_CLEARED`: Formal SPPB clearance confirmation and shipping line DO release.
18. `IMP_18_PORT_GATE_OUT`: SPPB + DO + Gate Pass presentation, TPS gate-out execution.
19. `IMP_19_TRUCKING_DELIVERY`: Inland trucking dispatch and delivery monitoring to consignee warehouse.
20. `IMP_20_POD_CONFIRMED`: Warehouse unlading, delivery receipt, and signed Proof of Delivery (POD) upload.
21. `IMP_21_COMPLETED`: Cost reconciliation, service and reimbursement invoicing, Odoo sync, and physical archiving.

#### Export Workflow (`EXPORT_BC30`) — 17 Sequential Stages
1. `EXP_01_INSTRUCTION_RECEIVED`: Shipping Instruction (SI) and booking confirmation received from exporter.
2. `EXP_02_DOCS_UPLOADED`: Invoice, PL, SI, and export permits uploaded to bundle.
3. `EXP_03_AUTO_EXTRACTED`: Document data extraction including export readiness (`kesiapanBarang`) and forex bank (`bankDevisa`).
4. `EXP_04_PARTIES_VALIDATED`: Exporter NIB, NPWP, and overseas buyer details verified against master contracts.
5. `EXP_05_HS_VALIDATED`: BTKI export tariff, export levy (Bea Keluar), and export restriction (Lartas Ekspor) checked.
6. `EXP_06_DRAFT_PEB`: Draft BC 3.0 prepared with export valuation (FOB).
7. `EXP_07_PEB_VALIDATED`: Pre-check schema validation against CEISA `/document/check`.
8. `EXP_08_CEISA_SUBMITTED`: User approval gate verification and PEB submission to CEISA.
9. `EXP_09_PEB_RESPONSE`: Registration number (NOPEN), Export Service Note (**NPE**), or Export Examination Note (**PPBE**) received.
10. `EXP_10_LOADING_PREP`: Empty container pickup from container depot and dispatch to exporter facility.
11. `EXP_11_STUFFING_DOCS`: Stuffing photo documentation (Empty container, Process, Full container, Carrier seal).
12. `EXP_12_CUSTOMS_EXAM`: Conditional physical inspection by customs if PPBE issued (skipped if immediate NPE).
13. `EXP_13_EXAM_SEAL_PHOTOS`: Customs re-sealing photos and Berita Acara upload (if inspected).
14. `EXP_14_GATE_IN_DEPARTED`: CY terminal gate-in with NPE receipt before container yard closing time.
15. `EXP_15_ETD_ATD_TRACKED`: Vessel departure monitoring (ETD vs ATD) and issuance of Final Bill of Lading.
16. `EXP_16_TRANSIT_MONITORED`: International ocean voyage tracking to port of destination.
17. `EXP_17_COMPLETED`: Export service invoicing, reimbursement settlement, and document closure.

---

### 4.3 Cross-Document Validation Engine (Rules V01–V07)

| Rule Code | Rule Name | Severity | Monitored Fields | Validation Logic | 5-Status Output Mapping |
|:---:|---|:---:|---|---|---|
| **V01** | B/L Consistency | `BLOCKING` | `bl_number`, `bill_of_lading` | Compares B/L number across all uploaded documents in the bundle. All references must be identical (case-insensitive). | `Valid`: identical; `Mismatch`: differing B/L numbers; `Missing Data`: B/L not detected. |
| **V02** | Weight Ratio & Tolerance | `WARNING` | `gross_weight`, `net_weight` | Ensures Net Weight <= Gross Weight. Verifies tolerance between Packing List declared weight and B/L manifest weight (within <= 0.5% tolerance). | `Valid`: net <= gross within tolerance; `Mismatch`: net > gross; `Warning`: variance > 0.5%. |
| **V03** | Invoice Header vs Line Items Sum | `WARNING` | `total_amount`, item prices | Sums line item quantities $\times$ unit prices and compares with header total invoice value. Tolerance is $1.00 for rounding. | `Valid`: difference <= $1.00; `Mismatch`: difference > $1.00; `Requires Manual Review`: unparsed prices. |
| **V04** | ISO 6346 Container Checksum | `WARNING` | `container_number` | Validates format against `/^[A-Z]{4}[0-9]{7}$/` and computes modulo-11 check digit arithmetic. | `Valid`: check digit passes; `Mismatch`: check digit invalid; `Warning`: format non-compliant. |
| **V05** | 8-Digit BTKI HS Code Validity | `BLOCKING` | `hs_code`, `kode_hs` | Strips formatting characters; verifies each item HS code is exactly 8 numeric digits and exists in the BTKI 2022 tariff table. | `Valid`: valid 8-digit BTKI code; `Mismatch`: non-8 digit or invalid tariff heading. |
| **V06** | Currency & KMK Customs Exchange Rate | `BLOCKING` | `currency`, `kode_valuta` | Checks currency code against recognized central bank list (`USD`, `EUR`, `CNY`, `SGD`, `JPY`, `GBP`, `AUD`, `MYR`) and verifies active KMK tax exchange rate availability. | `Valid`: currency recognized and active KMK rate available; `Mismatch`: unrecognized currency. |
| **V07** | Importer NPWP 16-Digit (PMK 112/2022) | `BLOCKING` | `importer_npwp`, `client_npwp` | Strips formatting; validates digit length is either 15 digits (legacy corporate NPWP) or 16 digits (PMK 112/2022 NIK/NPWP format). | `Valid`: 15 or 16 digits; `Mismatch`: invalid digit length (< 15 or > 16). |

#### 5-Status Engine Categorization
1. **`Valid`**: Data fields match across documents within specified mathematical and string tolerance.
2. **`Warning`**: Minor discrepancies that do not halt customs submission (e.g. slight weight variance <= 0.5%, non-critical format deviations).
3. **`Mismatch`**: Serious discrepancies (e.g. differing B/L numbers, net weight > gross weight, invoice total discrepancy > $1.00, invalid container checksum).
4. **`Missing Data`**: Mandatory fields required by customs declaration schema are absent from the document bundle.
5. **`Requires Manual Review`**: OCR confidence score is below 0.80, scanned pages are blurred, or commodity description is generic/ambiguous.

---

### 4.4 Security Hardening & Approval Gate Architecture

#### Fix for MCP Token Authentication (`app/Http/Controllers/Api/McpCustomsAgentController.php`)
1. Remove lines 68–70 returning `null` when `$rawToken` is absent.
2. In `authenticateMcpToken(Request $request)`:
   - If `$rawToken` is missing or empty, return `false`.
   - Compute `hash('sha256', $token)`.
   - Query `McpCredential::where('token_hash', $hash)->first()`.
   - Return `$credential` if valid, otherwise return `false`.
3. In `execute(Request $request)` and `sse(Request $request)`:
   - If `$auth === false`, immediately return HTTP 401 Unauthorized with standard JSON-RPC error `-32001`.
4. In `tools/call`:
   - Inspect tool name.
   - Enforce scope check:
     - Read tools require `shipments:read` or `customs:read`.
     - Analyze tools require `shipments:analyze` or `customs:analyze`.
     - Write tools require `shipments:write` or `customs:write`.
   - If `$auth->hasAnyScope($requiredScopes)` is false, return HTTP 403 Forbidden with JSON-RPC error `-32003`.

#### Protection for CEISA Endpoints (`routes/api.php`)
```php
Route::prefix('v1/ceisa')->group(function () {
    Route::post('/webhook', [CeisaApiController::class, 'handleWebhook'])->name('api.ceisa.webhook');
    
    // Protected Endpoints
    Route::middleware(['auth:sanctum', 'permission:ceisa.submit'])->group(function () {
        Route::post('/bc-20/submit', [CeisaApiController::class, 'submitBC20']);
        Route::post('/bc-30/submit', [CeisaApiController::class, 'submitBC30']);
    });
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/dokumen/{nomorAju}/status', [CeisaApiController::class, 'getStatus']);
        Route::get('/kurs/{valuta}', [CeisaApiController::class, 'getKurs']);
        Route::post('/kurs/sync', [CeisaApiController::class, 'syncKurs'])->middleware('permission:ceisa.manage');
        Route::get('/tarif-hs/{hsCode}', [CeisaApiController::class, 'getTarifHs']);
        Route::post('/tarif-hs/sync', [CeisaApiController::class, 'syncHsCodes'])->middleware('permission:ceisa.manage');
    });
});
```

#### Immutable User Approval Gate Token Mechanism
1. Add columns to `ceisa_documents`:
   - `approved_by_user_id CHAR(36) NULL`
   - `approved_at DATETIME NULL`
   - `approval_payload_hash VARCHAR(64) NULL` (SHA-256 of raw JSON payload)
   - `approval_token VARCHAR(80) NULL`
2. Approval Endpoint: `POST /shipments/{id}/approve-customs-draft`
   - Accessible only by users with role `R-DOC-SPV` or `R-MGR`.
   - Computes `$hash = hash('sha256', json_encode($document->raw_payload))`.
   - Generates `$token = 'APPR-' . strtoupper(Str::random(16)) . '-' . time()`.
   - Saves `approved_by_user_id = auth()->id()`, `approved_at = now()`, `approval_payload_hash = $hash`, `approval_token = $token`.
3. Submission Enforcement in `CeisaDocumentService::submit(CeisaDocument $doc)`:
   ```php
   if (empty($doc->approved_by_user_id) || empty($doc->approval_payload_hash)) {
       throw new \DomainException('Submission rejected: Customs declaration has not received supervisor approval.');
   }
   
   $currentPayloadHash = hash('sha256', json_encode($doc->raw_payload));
   if ($currentPayloadHash !== $doc->approval_payload_hash) {
       throw new \SecurityException('Submission rejected: Payload was modified after supervisor approval. Re-approval required.');
   }
   ```

---

### 4.5 Document Intelligence, Provenance & HS Code Workbench

#### Field-Level Provenance Schema (`import_fields`)
- `id`: UUID primary key.
- `document_id`: Foreign key to `import_documents`.
- `field_key`: Normalized field identifier (`shipper_name`, `consignee_npwp`, `bl_number`, `gross_weight`, etc.).
- `field_label`: Human-readable label.
- `original_value`: Verbatim text extracted by OCR/native parser (immutable).
- `corrected_value`: Value updated by operator (defaults to original value).
- `confidence_score`: Extraction confidence float (`0.00` to `1.00`).
- `page_number`: Integer page number where the field was located.
- `bounding_box`: JSON object `{x: float, y: float, width: float, height: float}` defining coordinate box on the page.
- `extraction_engine`: Engine identifier (`SMALOT_PDF`, `TESSERACT_OCR`, `VISION_AI`, `RULE_REGEX`).
- `status`: Lifecycle state (`DETECTED`, `EDITED`, `CONFIRMED`, `REJECTED`).
- `edited_by_id`: User ID of operator who modified the value.
- `edited_at`: Timestamp of correction.

#### HS Code Intelligence Workbench: 2-Panel Interface
- **Left Panel (Part 1 — Final Customs Declaration Data)**:
  - Commodity name and description as declared on Invoice/PL.
  - Final Selected 8-digit HS Code input field.
  - Tariff legal basis (MFN / Form E / ACFTA).
  - Calculated duty rates: BM (%), PPN (%), PPh (%), BK (%), and total estimated duties.
  - Review Status badge (`APPROVED`, `MANUAL_VERIFIED`, `PENDING`).
  - Override Reason textarea (mandatory when rejecting AI suggestion).
- **Right Panel (Part 2 — AI / BTKI Recommendation Engine)**:
  - Top-3 Ranked Suggestions:
    - **Rank #1**: HS Code, BTKI description, confidence score (e.g. `0.92`), match rationale (matched attributes: material, function, specifications), tariff snapshot (BM 0%, PPN 11%, PPh 2.5%, Lartas: Non-Lartas).
    - **Rank #2 & #3**: Alternative subheadings with comparative delta rationale.
  - Anti-hallucination warning banner: *"Confidence Score does not equate to Guaranteed Legal Accuracy. Verify technical specifications."*
  - Action Buttons:
    - `[ V ] Approve & Apply`: Copies recommended HS code and duty rates to Left Panel; sets status to `APPROVED`; logs decision.
    - `[ X ] Reject / Manual`: Keeps Left Panel in manual edit mode; opens override reason dialog; logs decision to `hs_code_override_logs`.

#### Database Schema: `hs_code_recommendations` & `hs_code_override_logs`
```sql
CREATE TABLE hs_code_recommendations (
    id CHAR(36) PRIMARY KEY,
    shipment_item_id CHAR(36) NOT NULL,
    rank TINYINT NOT NULL, -- 1, 2, 3
    recommended_hs_code VARCHAR(20) NOT NULL,
    btki_description TEXT NOT NULL,
    rationale TEXT NOT NULL,
    matched_characteristics JSON NULL,
    confidence_score DECIMAL(5, 4) NOT NULL,
    tariff_snapshot JSON NOT NULL, -- {bm: 0, ppn: 11, pph: 2.5, bk: 0, lartas: "Non-Lartas"}
    source_engine ENUM('RULE_BTKI', 'GENIE_AI', 'MCP_AI') NOT NULL DEFAULT 'RULE_BTKI',
    review_decision ENUM('PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (shipment_item_id) REFERENCES shipment_items(id) ON DELETE CASCADE,
    INDEX idx_item_rank (shipment_item_id, rank)
);

CREATE TABLE hs_code_override_logs (
    id CHAR(36) PRIMARY KEY,
    shipment_item_id CHAR(36) NOT NULL,
    ai_recommended_hs VARCHAR(20) NOT NULL,
    final_hs_code VARCHAR(20) NOT NULL,
    action ENUM('APPROVED', 'REJECTED_MANUAL') NOT NULL,
    override_reason TEXT NULL,
    user_id CHAR(36) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Immutable
    FOREIGN KEY (shipment_item_id) REFERENCES shipment_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

### 4.6 MCP Tools Specifications

#### 1. `get_document_list`
- **Scope Required**: `shipments:read`
- **Description**: Returns all uploaded documents for a given shipment, including classification, upload timestamps, verification status, and checklist of missing mandatory/conditional documents.
- **Parameters**:
  - `shipment_id` (string, required): Shipment UUID or Project No (e.g. `SHP-202611-0012`).
- **Response**:
  ```json
  {
    "shipment_id": "9d901a12-...",
    "project_no": "SHP-202611-0012",
    "total_documents": 7,
    "verified_documents": 5,
    "completion_rate_percent": 71,
    "documents": [
      {
        "id": "doc-uuid-1",
        "type": "INVOICE",
        "file_name": "COMMERCIAL_INVOICE_INV991.pdf",
        "status": "VERIFIED",
        "page_count": 2,
        "is_mandatory": true
      }
    ],
    "missing_mandatory": ["COO_FORME"],
    "missing_conditional": []
  }
  ```

#### 2. `get_inspection_history`
- **Scope Required**: `customs:read`
- **Description**: Retrieves customs inspection records (SPJM behandle, NPD, DNP, PPBE), scheduled dates, TPS location, old vs new seal numbers, and photo evidence links.
- **Parameters**:
  - `shipment_id` (string, required): Shipment UUID.
- **Response**:
  ```json
  {
    "shipment_id": "9d901a12-...",
    "inspections": [
      {
        "inspection_id": "insp-uuid-1",
        "inspection_type": "SPJM",
        "ticket_number": "SPJM-040300-20261112-0041",
        "scheduled_at": "2026-11-13 10:00:00",
        "location_tps": "TPS KOJA",
        "old_seal_number": "ML-INA982141",
        "new_seal_number": "BC-JKT-881249",
        "status": "COMPLETED",
        "photo_count": 8,
        "photos": [
          {
            "stage": "BEFORE_UNLOAD",
            "file_url": "/storage/inspections/...",
            "taken_at": "2026-11-13 10:15:00",
            "seal_number": "ML-INA982141"
          }
        ]
      }
    ]
  }
  ```

#### 3. `validate_pib_data`
- **Scope Required**: `customs:analyze`
- **Description**: Executes the cross-document validation ruleset (V01–V07) and CEISA BC 2.0 schema pre-check for an import shipment.
- **Parameters**:
  - `shipment_id` (string, required): Shipment UUID.
- **Response**:
  ```json
  {
    "shipment_id": "9d901a12-...",
    "overall_status": "VALID",
    "can_submit_ceisa": true,
    "rules_summary": {
      "passed": 6,
      "warnings": 1,
      "blocking_failed": 0
    },
    "details": [
      {"rule_code": "V01", "rule_name": "B/L Consistency", "status": "PASSED", "severity": "BLOCKING"},
      {"rule_code": "V02", "rule_name": "Weight Tolerance", "status": "WARNING", "severity": "WARNING", "message": "Variance 0.3% within 0.5% tolerance."}
    ],
    "ceisa_precheck": {
      "status_code": 200,
      "message": "JSON Schema Valid"
    }
  }
  ```

#### 4. `validate_peb_data`
- **Scope Required**: `customs:analyze`
- **Description**: Executes validation rules and CEISA BC 3.0 schema pre-check for an export shipment, checking export readiness (`kesiapanBarang`), forex bank (`bankDevisa`), and export duty (`bk`).
- **Parameters**:
  - `shipment_id` (string, required): Shipment UUID.
- **Response**:
  ```json
  {
    "shipment_id": "9d901a12-...",
    "overall_status": "VALID",
    "can_submit_ceisa": true,
    "export_readiness": "SIAP_PERIKSA",
    "bank_devisa": "BANK MANDIRI",
    "rules_summary": {"passed": 7, "warnings": 0, "blocking_failed": 0}
  }
  ```

#### 5. `compare_hs_code`
- **Scope Required**: `customs:analyze`
- **Description**: Compares candidate 8-digit BTKI codes against item descriptions, calculating duty differentials and summarizing Lartas restrictions.
- **Parameters**:
  - `item_description` (string, required): Product description.
  - `candidate_hs_codes` (array of strings, required): e.g. `["8412.21.00", "8430.69.00"]`.
  - `country_of_origin` (string, optional): e.g. `"CN"`.
- **Response**:
  ```json
  {
    "item_description": "Hydraulic Breaker Assembly DYB800",
    "comparison": [
      {
        "hs_code": "8412.21.00",
        "description": "Linear acting hydraulic power engines and motors (cylinders)",
        "bm": 0.0,
        "ppn": 11.0,
        "pph": 2.5,
        "lartas": "Bebas Lartas",
        "recommendation_score": 0.92
      },
      {
        "hs_code": "8430.69.00",
        "description": "Other moving, grading, levelling, scraping, excavating machinery",
        "bm": 0.0,
        "ppn": 11.0,
        "pph": 2.5,
        "lartas": "Bebas Lartas",
        "recommendation_score": 0.78
      }
    ]
  }
  ```

#### 6. `generate_compliance_report`
- **Scope Required**: `customs:analyze`
- **Description**: Generates an audit compliance report summarizing SLA free-time risks, document verification rates, inspection exposure, and invoice status.
- **Parameters**:
  - `shipment_id` (string, required): Shipment UUID.
- **Response**:
  ```json
  {
    "shipment_id": "9d901a12-...",
    "project_no": "SHP-202611-0012",
    "compliance_rating": "COMPLIANT_READY",
    "days_until_free_time_expired": 4,
    "demurrage_risk": "LOW",
    "customs_channel": "HIJAU",
    "three_way_matching": {"value_match": true, "weight_match": true, "packages_match": true},
    "lartas_compliance": {"has_lartas": false, "all_lartas_complete": true},
    "approval_status": "APPROVED_BY_SUPERVISOR"
  }
  ```

---

### 4.7 Field Operations, Inspection, Delivery & Financial Control

#### Database Schema: `customs_inspections`
```sql
CREATE TABLE customs_inspections (
    id CHAR(36) PRIMARY KEY,
    shipment_id CHAR(36) NOT NULL,
    container_id CHAR(36) NULL,
    inspection_type ENUM('SPJM', 'NPD', 'DNP', 'NHI', 'PPBE') NOT NULL,
    ticket_number VARCHAR(100) NOT NULL,
    issued_at DATETIME NOT NULL,
    scheduled_at DATETIME NOT NULL,
    location_tps VARCHAR(100) NOT NULL,
    inspector_name VARCHAR(150) NULL,
    exam_notes TEXT NULL,
    old_seal_no VARCHAR(60) NOT NULL,
    new_seal_no VARCHAR(60) NULL,
    ba_file_path VARCHAR(255) NULL, -- Berita Acara file path
    slim_uploaded_at DATETIME NULL,  -- Timestamp uploaded to customs SLIM portal
    status ENUM('SCHEDULED', 'IN_PROGRESS', 'RESEALED', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'SCHEDULED',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (container_id) REFERENCES shipment_containers(id) ON DELETE SET NULL,
    INDEX idx_inspection_shipment (shipment_id, inspection_type)
);
```

#### Photo Evidence Stages
- **Import Stages (`flow_type = 'IMPORT_SPJM'`)**:
  1. `BEFORE_UNLOAD`: Container exterior, initial carrier seal intact on locking bar.
  2. `UNLOADING`: Container doors open, first row of cargo visible.
  3. `GOODS_INSPECTION`: Sample boxes unpacked, item labels, markings, serial numbers.
  4. `RESEALING`: Container closed, new Bea Cukai bullet seal (`new_seal_no`) attached.
  5. `INSPECTION_REPORT`: Signed physical Berita Acara Pemeriksaan (BA).
- **Export Stages (`flow_type = 'EXPORT_STUFFING'`)**:
  1. `EMPTY_CONTAINER`: Clean empty container floor and walls before loading.
  2. `STUFFING_PROCESS`: Cargo loading in progress, dunnage/lashing.
  3. `FULL_CONTAINER`: Loaded cargo reaching doors.
  4. `BC_SEAL`: Final carrier or Bea Cukai seal attached.

#### Database Schema: `shipment_deliveries`
```sql
CREATE TABLE shipment_deliveries (
    id CHAR(36) PRIMARY KEY,
    shipment_id CHAR(36) NOT NULL,
    container_id CHAR(36) NULL,
    trucking_vendor_name VARCHAR(150) NOT NULL,
    driver_name VARCHAR(120) NOT NULL,
    driver_phone VARCHAR(30) NULL,
    plate_number VARCHAR(20) NOT NULL,
    gate_out_at DATETIME NOT NULL,
    arrived_at DATETIME NULL,
    pod_signed_by VARCHAR(120) NULL,
    pod_file_path VARCHAR(255) NULL,
    pod_notes TEXT NULL,
    delivery_status ENUM('GATE_OUT', 'IN_TRANSIT', 'DELIVERED_POD', 'EXCEPTION') NOT NULL DEFAULT 'GATE_OUT',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (container_id) REFERENCES shipment_containers(id) ON DELETE SET NULL
);
```

#### Port Operational Cost Reconciliation
- Uses table `shipment_operation_costs`:
  - Categories: `DO` (Release Fee), `EXTEND_DO_1`, `EXTEND_DO_2`, `EXTEND_DO_3`, `REFUND` (Container Deposit Refund), `BHDL` (Behandle/Stripping Handling), `STG` (Storage/Penumpukan TPS), `TKC` (Truck Cleaning), `LOLO` (Lift-On/Lift-Off), `TRUCKING`.
  - Reconciles with `shipments.invoice_jasa_no` (handling fee invoice) and `shipments.invoice_reimburse_no` (port disbursement reimburse invoice).
  - Syncs closing status with Odoo (`shipments.odoo_synced = true`).

---

### 4.8 CEISA 4.0 API Integration Specifications

#### Gateway Communication Architecture
- **Base URL**: `config('ceisa.api.base_url')` (Production: `https://apis-gw.beacukai.go.id/v2/openapi`).
- **Auth URL**: `config('ceisa.api.auth_url')` (`/v1/openapi-auth/user/login`).
- **Headers**:
  - `Content-Type: application/json`
  - `beacukai-api-key: <API_KEY>`
  - `Application-ID: <APP_ID>`
  - `Authorization: Bearer <CACHED_JWT_TOKEN>`

#### Queue Retry Backoff & Rate Limiting
1. **Asynchronous Job Wrapper**: `SubmitCeisaDocumentJob` and `SyncCeisaStatusJob`.
2. **Exponential Backoff Schedule**:
   - Retry 1: 10 seconds.
   - Retry 2: 30 seconds.
   - Retry 3: 120 seconds.
   - Max Attempts: 3.
   - Retries apply exclusively to transient network errors and HTTP codes: `429` (Rate Limited), `502` (Bad Gateway), `503` (Service Unavailable), and `504` (Gateway Timeout).
   - Rejection on HTTP `400` / `422` (Schema error) is permanent: marks document as `REJECT`, updates `status_description`, and logs error in `ceisa_api_logs`.
3. **Rate Limiting**:
   ```php
   RateLimiter::for('ceisa-api', function ($job) {
       return Limit::perMinute(60)->by('ceisa_gateway');
   });
   ```
4. **Idempotency Guarantee**:
   All submit requests utilize the 26-character unique customs registration key `nomor_aju`. Before dispatching, the job verifies that `ceisa_documents.status` is not already `SUBMITTED`, `PROSES`, or `SELESAI`, preventing duplicate customs registrations.

#### Mock Mode Simulator Specifications (`config/ceisa.php`)
- When `mock_mode = true`:
  - `POST /document/check`: Validates presence of `nomorBc11` and `posBc11` (for BC 2.0) or `kesiapanBarang` and `bankDevisa` (for BC 3.0). Returns HTTP 200 with JSON schema valid message.
  - `POST /document`: Returns HTTP 200 with simulated `idHeader` UUID, matching `nomorAju`, and `waktuTerima`.
  - `GET /status/{nomorAju}`: Generates simulated customs registration number (`nomorDaftar`), registration date, response code (`SPPB` for BC 2.0 or `NPE` for BC 3.0), and billing code (`kodeBilling`).
  - All mock transactions are stored in `ceisa_api_logs` with header `is_mock = true`.
