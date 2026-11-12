# Core Architecture Investigation Report: GENIE Export-Import Tracking & Automation

**State Folder**: `c:\laragon\www\GENIE\.agents\teamwork\explorer_codebase_core_2`  
**Author**: Core Architecture Explorer (Gen 2 replacement)  
**Date**: 2026-11-12T15:35:00Z  
**Target Scope**: Data models, migrations, container management, state machines, cross-document validations, extraction/OCR pipeline, security gates, and test suite health.

---

## 1. Observation

### 1.1 Data Models & Container Architecture
- **Existing Models in `app/Models/`**:
  - `Shipment.php` (844 lines, defines primary project metadata, 68 database columns, 54 `$fillable` attributes).
  - `ShipmentDocument.php` (tracks document attachments per shipment with `jenis_dokumen`, `version`, `is_current`, `status`).
  - `ShipmentItem.php` (stores item lines, `kode_hs`, `hs_confidence_score`, `nilai_fob`, `tarif_bm_persen`, etc.).
  - `InspectionPhoto.php` (stores inspection photos with `flow_type`, `stage`, `seal_number`, `taken_at`).
  - `ShipmentOperationCost.php` (stores costs: `DO`, `EXTEND_DO_*`, `BHDL`, `LOLO`, etc.).
  - `ImportBatch.php`, `ImportFile.php`, `ImportDocument.php`, `ImportField.php`, `ImportItem.php`, `ImportValidation.php`, `ImportApplication.php` (Sprint 2 F15 batch import models).
  - `CeisaDocument.php`, `CeisaApiLog.php`, `CeisaExchangeRate.php` (CEISA 4.0 integration models).
  - **CRITICAL GAP**: There is **NO** `ShipmentContainer.php` or `Container.php` Eloquent model in `app/Models/`.
- **Database Schema for Containers**:
  - `shipments` table stores container info as free text / single string:
    - Column `nomor_kontainer`: `VARCHAR(100)` nullable (e.g. `'WHLU-9182341 (1x40HC)'`, `'OOLU9081726'`).
    - Column `total_kontainer`: `INT` default `1`.
    - Column `old_seal_number`: `VARCHAR(60)` nullable.
    - Column `new_seal_number`: `VARCHAR(60)` nullable.
  - `shipment_containers` table **already exists in the database** (created by migration `2026_10_12_000013_create_f4_delivery_order_tables.php`, lines 46–55):
    - Existing columns: `id` (UUID), `shipment_id` (foreignUuid), `voyage_id` (foreignUuid nullable), `number` (`VARCHAR(255)`), `type_id` (foreignUuid nullable), `seal_no` (`VARCHAR(255)` nullable), `created_at`, `updated_at`.
    - However, `shipment_containers` is **completely orphaned**: zero Eloquent models reference it, zero controllers query it, and zero blade views bind to it.
  - Over 15 Blade views directly render `shipments.nomor_kontainer` as raw text:
    - `resources/views/dashboard.blade.php:174`: `{{ $shp->nomor_kontainer ?? '1 Kontainer' }}`
    - `resources/views/core/tracking_index.blade.php:281`: `{{ $s->nomor_kontainer ?: '-' }}`
    - `resources/views/bc20/create.blade.php:604`: `value="{{ old('nomor_kontainer', $selectedShipment->nomor_kontainer ?? 'TGHU8192031') }}"`
    - `resources/views/bc30/create.blade.php:580`: `value="{{ old('nomor_kontainer', $selectedShipment->nomor_kontainer ?? 'EMCU9820192') }}"`
    - `resources/views/core/traffic_index.blade.php:153`: `openPhotoInspectionModal(..., '{{ $shp->nomor_kontainer }}', '{{ $shp->new_seal_number }}')`

### 1.2 State Machine & Workflow Transitions
- **Single Status Field**:
  - `shipments.status_tahap` is a `VARCHAR(50)` (loosened from ENUM by `2026_11_12_100000_add_tracking_dok_and_inspection_photos_tables.php`).
  - Import (`IMPORT_BC20`) and Export (`EKSPORT_BC30`) share the **exact same** column and lifecycle stages.
- **Current Pipeline Implementation**:
  - Defined in `Shipment::STAGES_PIPELINE` (`app/Models/Shipment.php:561–573`) as an 11-stage UI pipeline:
    - `0: doc`, `1: hs`, `2: pib`, `3: bill`, `4: do`, `5: arr`, `6: hold`, `7: gate`, `8: send`, `9: pod`, `10: inv`.
  - Mapped in `Shipment::getStageIndexAttribute()` (`app/Models/Shipment.php:578–598`) using `match ($this->status_tahap)`.
  - Status updates occur imperatively inside `ShipmentWorkflowService.php`:
    - `createProject`: sets `DOC_COLLECTING`, `PENDING_REQUEST`.
    - `createProjectFromUploadedDocuments`: sets `HS_DETECTED` or `DOC_READY`.
    - `verifyDocument` / `verifyAllDocuments`: transitions `DOC_COLLECTING` -> `DOC_READY`.
    - `detectHsCodeFromPackingList`: sets `HS_DETECTED`.
    - `submitCeisaAndAdvanceToDo`: transitions to `BILLING_ISSUED`.
    - `sendVendorEmailForDo`: sets `DO_PROCESSING`.
    - `releaseDo`: sets `DO_RELEASED`.
    - `advanceTraffic`: sets `TRAFFIC_SPPB`, `TRAFFIC_HOLD`, `TRAFFIC_GATE_OUT`, or `DELIVERED_POD`.
    - `generateClientInvoice`: sets `INVOICED_P19`.
- **Missing Architecture**:
  - No formal State Machine classes or transition guards exist.
  - The blueprint-specified 21-stage Import workflow (`IMP_01_DOC_RECEIVED` to `IMP_21_COMPLETED`) and 17-stage Export workflow (`EXP_01_INSTRUCTION_RECEIVED` to `EXP_17_COMPLETED`) are completely absent from the domain layer.

### 1.3 Cross-Document Validation Engine (V01–V07)
- **Service Implementation**:
  - `app/Services/Shipment/CrossDocumentValidationService.php` contains implementations for:
    - `ruleV01BlConsistency` (BLOCKING)
    - `ruleV02WeightTolerance` (WARNING)
    - `ruleV03InvoiceItemsSum` (WARNING)
    - `ruleV04ContainerChecksum` (WARNING, ISO 6346 format regex)
    - `ruleV05HsCodeBtki` (BLOCKING, 8-digit BTKI check)
    - `ruleV06CurrencyAndKmk` (BLOCKING, foreign currency check)
    - `ruleV07Npwp16Digit` (BLOCKING, 15/16 digit NPWP check)
- **Disconnection & Schema Mismatch**:
  - `CrossDocumentValidationService::validateDocument()` accepts strictly `ImportDocument $document`.
  - It is **only called** in `BatchImportService::validateDocumentRules()` (`app/Services/Shipment/BatchImportService.php:250`).
  - It is **never called** in `ShipmentWorkflowService`, `ShipmentController`, `TrackingDokController`, or `CeisaApiController`.
  - Line 44–56 writes `rule_name`, `status`, and `field_references` to `ImportValidation::updateOrCreate()`. However, inspection of the `import_validations` table (`2026_11_12_000002_sprint_2_f15_f16_core_engine.php:113–122`) reveals only:
    `id`, `document_id`, `rule_code`, `severity`, `message`, `is_passed`, `created_at`, `updated_at`.
    Columns `rule_name`, `status`, and `field_references` do not exist in the database!

### 1.4 Document Extraction, OCR & Provenance
- **`DocumentExtractionService.php` (1,534 lines)**:
  - Text extraction from PDF relies on `Smalot\PdfParser\Parser` and uncompressed stream fallbacks.
  - For image files (`jpg`, `jpeg`, `png`, `webp`, `bmp`), lines 312–314 explicitly return a mock placeholder:
    `return "IMAGE_FILE: {$origName} (OCR Image Parsing Active)";`
    No Tesseract OCR or Vision AI is invoked.
  - The directory `app/Jobs/` does not exist. There are no queued asynchronous OCR or extraction jobs.
- **Provenance Data Structure**:
  - `ImportField` (`app/Models/ImportField.php`) supports `field_key`, `original_value`, `corrected_value`, `confidence_score`, `page_number`, and `bounding_box` (JSON).
  - However, when a shipment is created through `ShipmentWorkflowService::createProjectFromUploadedDocuments()`, extraction results are immediately flattened into `Shipment` table columns (`nomor_bl_awb`, `nomor_kontainer`, `total_bruto`, etc.), discarding `document_id`, `page_number`, `bounding_box`, and confidence scores.

### 1.5 Security Flaws & CEISA Guardrails
- **MCP Endpoints (`routes/api.php:18–27`)**:
  - None of the `/api/mcp/*` endpoints are protected by authentication middleware (`auth:sanctum` or token scope middleware).
  - In `app/Http/Controllers/Api/McpCustomsAgentController.php:68–70`:
    ```php
    if (!$rawToken) {
        return null; // Session internal atau unauthenticated call
    }
    ```
    In `sse()` (line 94) and `execute()` (line 127):
    `if ($auth === false)` only traps explicitly invalid tokens. When `$auth === null` (no token provided), the check passes! Unauthenticated external callers can list tools and execute tools.
- **CEISA Submission Endpoints (`routes/api.php:8–9`)**:
  - `POST /api/v1/ceisa/bc-20/submit` and `POST /bc-30/submit` have **zero middleware** (completely open to public HTTP POST requests).
- **CEISA Approval Gate**:
  - `CeisaDocumentService::submit()` (`app/Services/Ceisa/CeisaDocumentService.php:52–94`) only checks `config('ceisa.api.read_only')`. It does not require or verify an approval token or `approved_by_user_id`.

### 1.6 Test Suite Execution Health
- `BundleTrackingAndInspectionTest` passed 100% cleanly:
  `{"tool":"phpunit","result":"passed","tests":2,"passed":2,"assertions":39,"duration_ms":9638}`
- Full suite execution (`php artisan test`) revealed:
  - Several tests use `RefreshDatabase` which executes `migrate:fresh` against MySQL. When tests run or fail, tables can be dropped and left unmigrated.
  - `AccountingService.php:371` has a runtime bug: `ErrorException: Undefined variable $entryDate`, causing `AdminPanelRoutesTest` to return HTTP 500.
  - `phpunit.xml` has SQLite in-memory commented out, forcing all tests directly onto MySQL (`PATS_CEISA`).

---

## 2. Logic Chain

1. **Multi-Container Normalization (R1)**:
   - *Observation*: `shipment_containers` table already exists with 8 columns, while `shipments.nomor_kontainer` is a VARCHAR(100) used across 15+ views. No Eloquent model exists.
   - *Deduction*: To achieve normalization without breaking existing views, a new migration must add the missing columns (`container_number`, `container_size`, `container_type`, `initial_seal_no`, `bc_reseal_no`, `gate_out_at`, `delivered_at`, `eir_file_path`) to `shipment_containers`. An Eloquent model `ShipmentContainer` must be created.
   - *Compatibility Strategy*: On `Shipment.php`, an accessor and mutator for `nomor_kontainer` must bridge single-string access with `shipment_containers` (e.g. returning the first container or comma-separated numbers, and on save auto-populating `shipment_containers`).

2. **Workflow Separation (`IMPORT_BC20` vs `EXPORT_BC30`) (R1)**:
   - *Observation*: Both workflows currently use `shipments.status_tahap` and an 11-stage pipeline.
   - *Deduction*: An Import shipment undergoes customs clearance, SPJM/SPPB, behandle, and DO release; an Export shipment undergoes booking confirmation, stuffing, PEB declaration, PPBE/NPE, and port gate-in. Merging them into a single linear pipeline causes state pollution.
   - *Implementation Path*: Introduce dedicated state machines `ImportWorkflowStateMachine` (`IMP_01` to `IMP_21`) and `ExportWorkflowStateMachine` (`EXP_01` to `EXP_17`). Use a polymorphic or strategy pattern in `ShipmentWorkflowService` based on `shipment.jenis_layanan`.

3. **Cross-Document Validation Integration (R1 & R3)**:
   - *Observation*: `CrossDocumentValidationService` implements rules V01–V07, but is coupled strictly to `ImportDocument` and is never executed in the primary `Shipment` pipeline.
   - *Deduction*: The engine cannot protect CEISA submissions or alert operators in `TrackingDok` unless it operates on `Shipment` bundles.
   - *Implementation Path*: Refactor `CrossDocumentValidationService` to accept `Shipment` (evaluating its documents, items, containers, and client metadata) and output a standardized 5-status result array (`Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review`). Hook this into `ShipmentWorkflowService::uploadDocument()`, `reSyncShipmentFromDocuments()`, and pre-CEISA checks.

4. **Security & Human Approval Gate (R2)**:
   - *Observation*: `McpCustomsAgentController` treats null tokens as permitted; `/api/v1/ceisa/*` lacks auth; `CeisaDocumentService::submit()` has no approval token check.
   - *Deduction*: This exposes internal administrative and customs actions to unauthorized remote execution and accidental auto-declarations to DJBC.
   - *Implementation Path*: Fix `authenticateMcpToken()` to require valid Bearer tokens for all external requests. Add `auth:sanctum` and permission middleware (`ceisa.submit`) to CEISA routes. Require an immutable approval record (`draft_approved_by`, `draft_approved_at`, `approval_token`) before `CeisaDocumentService::submit()` is permitted to execute.

5. **Document Provenance & OCR (R3)**:
   - *Observation*: Image files return dummy strings; extraction flattens values into `shipments` without retaining page or bounding box data; `app/Jobs/` is absent.
   - *Deduction*: Operators cannot trace where fields originated, and scans cannot be processed asynchronously.
   - *Implementation Path*: Connect the `import_batches`/`import_files`/`import_fields` schema into the shipment creation flow so that every extracted field links to `document_id`, `page_number`, `bounding_box`, and `confidence_score`. Introduce a queued job `ProcessDocumentOcrJob` in `app/Jobs/`.

---

## 3. Caveats

- **Active MySQL Test Environment**: Because `phpunit.xml` targets MySQL and multiple tests use `RefreshDatabase`, running `php artisan test` can drop and re-migrate the database. Before and after testing, ensure `php artisan migrate --force` is confirmed.
- **External DJBC Sandbox Credentials**: CEISA integration currently runs with `mock_mode = true` and `read_only = true` in `config/ceisa.php`. Sandbox endpoints cannot be live-tested without valid DJBC gateway credentials.
- **Historical Data**: Existing shipments in `shipments` have raw strings in `nomor_kontainer`. A data migration seeder will be required to parse existing strings into `shipment_containers` rows.

---

## 4. Conclusion

The GENIE codebase has a strong operational foundation with extensive domain logic for CEISA 4.0, customs documents, and financial tracking. However, critical architectural gaps must be closed:
1. **Container Normalization**: Create `app/Models/ShipmentContainer.php` and migration to expand `shipment_containers` with ISO 6346 validation and backward-compatible `Shipment` accessors.
2. **State Machine Separation**: Create explicit `ImportWorkflowStateMachine` (21 stages) and `ExportWorkflowStateMachine` (17 stages) to decouple import and export flows.
3. **Cross-Document Validation**: Elevate `CrossDocumentValidationService` (V01–V07) to validate `Shipment` instances and enforce blocking checks before CEISA submission.
4. **Security Hardening**: Enforce strict token checks on `/api/mcp/*`, protect `/api/v1/ceisa/*` with Sanctum and permissions, and add a mandatory Human Approval Gate token before submission.
5. **Provenance & Jobs**: Implement `app/Jobs/` for async OCR and persist field provenance (`document_id`, `page_number`, `bounding_box`, `confidence_score`).

---

## 5. Verification Method

To independently verify all findings in this report:

1. **Verify Missing Model & Existing Table**:
   ```powershell
   php artisan tinker --execute="dump(class_exists('App\Models\ShipmentContainer')); dump(Schema::getColumnListing('shipment_containers'));"
   ```
   *Expected*: `false` for class existence; lists existing 8 columns for table.

2. **Verify MCP Token Bypass Vulnerability**:
   Inspect `app/Http/Controllers/Api/McpCustomsAgentController.php`, lines 68–70 and 126–128.
   Execute an unauthenticated POST request to `/api/mcp/execute` without a token header.
   *Expected*: Request does not return 401; returns JSON-RPC response or method routing.

3. **Verify Disconnected `CrossDocumentValidationService`**:
   ```powershell
   Select-String -Path "app\Services\*.php", "app\Http\Controllers\*.php" -Pattern "CrossDocumentValidationService"
   ```
   *Expected*: Only matches within `BatchImportService.php` and its own declaration; 0 matches in `ShipmentWorkflowService` or controllers.

4. **Verify Test Health**:
   ```powershell
   php artisan test --filter BundleTrackingAndInspectionTest
   ```
   *Expected*: Passes (2 tests, 39 assertions).

5. **Verify Database State**:
   ```powershell
   php artisan migrate:status
   ```
   *Expected*: All 32 migrations display `[Ran]`.
