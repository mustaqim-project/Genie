# Integrations & Security Explorer Handoff Report

## 1. Observation

### 1.1 Scope 1: MCP Endpoints, Authentication, Scopes & Registered Tools
1. **Route Declaration without Middleware (`routes/api.php:18-27`)**:
   ```php
   // MCP (Model Context Protocol) Server & Customs AI Agent Endpoints
   Route::prefix('mcp')->group(function () {
       Route::get('/sse', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'sse'])->name('api.mcp.sse');
       Route::get('/tools', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'tools'])->name('api.mcp.tools');
       Route::get('/manifest', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'tools']);
       Route::post('/execute', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'execute'])->name('api.mcp.execute');
       Route::post('/parse-packing-list', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'parsePackingList'])->name('api.mcp.parse-packing-list');
       Route::post('/check-hs', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'checkHs'])->name('api.mcp.check-hs');
       Route::post('/shipments/{id}/detect-hs', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'detectShipmentCustoms'])->name('api.mcp.shipments.detect-hs');
       Route::post('/shipments/{id}/generate-declaration', [\App\Http\Controllers\Api\McpCustomsAgentController::class, 'generateDeclaration'])->name('api.mcp.shipments.generate-declaration');
   });
   ```
   No authentication or scope middleware is assigned to the `/mcp` route group.

2. **Authentication Bypass in Controller (`app/Http/Controllers/Api/McpCustomsAgentController.php:60-86, 126-136, 198-215`)**:
   - `authenticateMcpToken(Request $request)` returns `null` when no token is supplied in header or query:
     ```php
     if (!$rawToken) {
         return null; // Session internal atau unauthenticated call
     }
     ```
   - In `execute()`:
     ```php
     $auth = $this->authenticateMcpToken($request);
     if ($auth === false) {
         return response()->json(['error' => ['code' => -32001, 'message' => 'Unauthorized...']], 401);
     }
     ...
     // Otorisasi Granular berbasis Scope MCP Token (RBAC Guard)
     if ($auth instanceof \App\Models\McpCredential) {
         $requiredScopes = $this->getRequiredScopesForTool((string) $toolName, is_array($args) ? $args : []);
         if (!$auth->hasAnyScope($requiredScopes)) {
             ...
             return response()->json(['error' => ['code' => -32003, ...]], 403);
         }
     }

     try {
         $tool = $this->toolRegistry->getTool($toolName);
         $toolResult = $tool->execute(is_array($args) ? $args : []);
     }
     ```
     When an unauthenticated request is sent without a token, `$auth` is `null`. The condition `if ($auth === false)` evaluates to `false`. The condition `if ($auth instanceof \App\Models\McpCredential)` evaluates to `false`. Therefore, scope verification is skipped entirely, and the requested tool is executed unconditionally with `credential_id => null, provider => 'internal-web'`.
   - In `parsePackingList`, `checkHs`, `detectShipmentCustoms`, and `generateDeclaration`, `authenticateMcpToken()` is never invoked. Any external client can invoke these endpoints directly.

3. **Missing MCP Tools (`app/Domain/Mcp/Tools/McpToolRegistry.php:28-74`)**:
   The registry initializes 25 tools across customs, shipments, DO, traffic, finance, tax, CRM, HRM, and governance. However, the 6 required tools specified in R3 are missing:
   - `get_document_list`: Not present.
   - `get_inspection_history`: Not present.
   - `validate_pib_data`: Not present.
   - `validate_peb_data`: Not present.
   - `compare_hs_code`: Not present.
   - `generate_compliance_report`: Not present.

---

### 1.2 Scope 2: CEISA 4.0 Integration, Config, Queues & Mock Mode
1. **Unauthenticated CEISA Endpoints (`routes/api.php:6-15`)**:
   ```php
   Route::prefix('v1/ceisa')->group(function () {
       Route::post('/webhook', [CeisaApiController::class, 'handleWebhook'])->name('api.ceisa.webhook');
       Route::post('/bc-20/submit', [CeisaApiController::class, 'submitBC20']);
       Route::post('/bc-30/submit', [CeisaApiController::class, 'submitBC30']);
       Route::get('/dokumen/{nomorAju}/status', [CeisaApiController::class, 'getStatus']);
       Route::get('/kurs/{valuta}', [CeisaApiController::class, 'getKurs']);
       Route::post('/kurs/sync', [CeisaApiController::class, 'syncKurs']);
       Route::get('/tarif-hs/{hsCode}', [CeisaApiController::class, 'getTarifHs']);
       Route::post('/tarif-hs/sync', [CeisaApiController::class, 'syncHsCodes']);
   });
   ```
   All `/api/v1/ceisa/*` endpoints are exposed publicly without `auth:sanctum` or role/permission checks.

2. **Synchronous Execution & Missing Queues (`app/Http/Controllers/Api/CeisaApiController.php:87, 158`)**:
   - Both `submitBC20` and `submitBC30` call `$this->documentService->submit($doc)` synchronously inside the controller method.
   - The directory `app/Jobs/` does not exist in the project repository.
   - `.env` specifies `QUEUE_CONNECTION=sync`.

3. **Resilience & Rate Limiting Gap (`app/Services/Ceisa/CeisaClientService.php:93-130`)**:
   - HTTP requests to DJBC gateway in `CeisaClientService::send()` do not utilize retry backoff, exponential backoff, or rate limiting. Any network timeout or gateway 5xx error directly produces `['status_code' => 500, 'success' => false]`.

4. **Mock Mode vs Read-Only Precedence Bug (`app/Services/Ceisa/CeisaClientService.php:57, 83`)**:
   - Line 57:
     ```php
     if ($httpMethod !== 'GET' && (bool) config('ceisa.api.read_only', true)) {
         return $blockedResponse; // 403 Forbidden
     }
     ```
   - Line 83:
     ```php
     if ((bool) config('ceisa.api.mock_mode', $this->mockMode)) {
         ...
         return $mockResponse;
     }
     ```
   - In `config/ceisa.php`, default values are `'read_only' => env('CEISA_READ_ONLY', true)` and `'mock_mode' => env('CEISA_MOCK_MODE', true)`. Because the `read_only` check precedes the `mock_mode` check, local mock submissions return HTTP 403 Forbidden unless `CEISA_READ_ONLY=false` is set in the environment.

---

### 1.3 Scope 3: Human Approval Gate Mechanism
1. **Direct Submission without Approval Verification (`app/Services/Ceisa/CeisaDocumentService.php:52-77`)**:
   - `CeisaDocumentService::submit(CeisaDocument $document)` prepares the payload and directly posts to `/document` without checking whether an authorized user has approved the declaration.
2. **Missing Model Schema (`app/Models/CeisaDocument.php:17-63`)**:
   - `CeisaDocument` has no approval attributes (e.g. `approval_token`, `approval_status`, `approved_by_user_id`, `approved_at`, `approval_notes`).
3. **Pseudo-Approval in MCP Tool (`app/Domain/Mcp/Tools/McpApproveActionTool.php:78-95`)**:
   - When called with `target_type = CUSTOMS_DECLARATION`, it updates `shipment.status_tahap = 'EDI_SUBMITTED'` and sets `shipment.catatan`. No verifiable approval token is generated, stored, or checked against `CeisaDocument`.

---

### 1.4 Scope 4: HS Code Workbench & Intelligence
1. **Rule Engine & Confidence Score (`app/Services/Ceisa/CeisaHsCodeService.php:861-940`)**:
   - `classifyCommodity()` uses hardcoded regex and keyword comparisons (e.g., `str_contains($upper, 'BREAKER')`) against a static `$builtInKnowledgeBase` array.
   - It assigns hardcoded confidence scores (`0.99` or `0.95`).
   - It only returns a single classification result rather than Top-3 ranked recommendations with reasoning.
2. **Missing Audit Override Table & Model**:
   - No `hs_code_override_logs` table exists in `database/migrations/`.
   - No `HsCodeOverrideLog` model exists in `app/Models/`.
3. **Missing Interactive Workbench & Evaluation Suite**:
   - No two-panel UI component exists for user Approve (`V`) / Reject (`X`) decisions.
   - No accuracy dataset or automated evaluation test exists to benchmark Top-1 and Top-3 accuracy against ground truth.

---

### 1.5 Scope 5: Field Operations, Inspection, Delivery & Financial Costs
1. **Customs Inspections & Seal Recording**:
   - No `customs_inspections` table exists.
   - Migration `2026_11_12_100000_add_tracking_dok_and_inspection_photos_tables.php` implemented `inspection_photos` and added flat columns to `shipments`:
     `spjm_received`, `spjm_ticket`, `spjm_received_at`, `inspection_scheduled_at`, `old_seal_number`, `new_seal_number`.
   - `InspectionPhoto` model stores multi-stage evidence (`BEFORE_UNLOAD`, `UNLOADING`, `GOODS_INSPECTION`, `RESEALING`, `INSPECTION_REPORT`, `EMPTY_CONTAINER`, `STUFFING_PROCESS`, `FULL_CONTAINER`, `BC_SEAL`).
2. **Delivery Tracking**:
   - No `shipment_deliveries` table or model exists.
   - Migration `2026_10_12_000014_create_f5_traffic_field_tables.php` contains schema definitions for `deliveries` and `pod_records`, but no corresponding Eloquent models exist, and the migration is currently `Pending`.
   - Active delivery tracking relies on flat fields on `shipments` (`delivery_date`, `pod_signed_at`, `trucking_vendor_name`).
3. **Port Operational Costs & Invoicing**:
   - `shipment_operation_costs` table and `ShipmentOperationCost` model exist with categories:
     `DO`, `EXTEND_DO_1..3`, `REFUND`, `BHDL`, `STG`, `TKC`, `LOLO`, `TRUCKING`, `OTHER`.
   - No reconciliation logic exists connecting `shipment_operation_costs` to customer billing (`ClientInvoice`, `invoice_jasa_no`, `invoice_reimburse_no`).
4. **Multi-Container Normalization**:
   - `shipments.nomor_kontainer` remains a single text column. A `shipment_containers` table was defined in migration `2026_10_12_000013`, but no Eloquent model exists.

---

### 1.6 Scope 6: Existing Test Suites & Migration Status
1. **Migration State (`php artisan migrate:status`)**:
   - Output reveals that migrations from `2026_10_12_000014` through `2026_11_12_100000` (19 migration files in total) are in `Pending` state in the active MySQL database `PATS_CEISA`.
2. **Test Suite Execution**:
   - `php artisan test --filter=BundleTrackingAndInspectionTest`: **Passed** (2 tests, 39 assertions).
   - `php artisan test --filter=CeisaSyncAndCallbackTest`: **Failed** with:
     ```
     SQLSTATE[42S22]: Column not found: 1054 Unknown column 'customs_channel' in 'field list'
     (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: PATS_CEISA,
      SQL: update `shipments` set `status_tahap` = TRAFFIC_SPPB, `customs_channel` = HIJAU ... where `id` = ...)
     ```
     This failure directly stems from migration `2026_10_19_225658_add_pipeline_fields_to_shipments_table.php` being `Pending`.
3. **MCP Test Coverage**:
   - No test file exists for MCP routes, controller methods, or security middleware.

---

## 2. Logic Chain

1. **Vulnerability in MCP Authorization (Obs 1.1.1, 1.1.2)**:
   Because `routes/api.php` attaches no middleware to `/api/mcp/*`, request handling falls directly to `McpCustomsAgentController`. In `authenticateMcpToken()`, an absent token produces `null`. In `execute()`, line 198 only checks scopes if `$auth instanceof McpCredential`. Since `null` is not an instance of `McpCredential`, the scope check is bypassed entirely. As a result, any unauthenticated attacker can execute sensitive MCP tools (e.g., `mcp_approve_action`, `mcp_delete_record`) without credentials.

2. **Vulnerability in CEISA API Protection (Obs 1.2.1, 1.2.2)**:
   Endpoints under `/api/v1/ceisa/*` lack middleware, leaving `POST /bc-20/submit` and `/bc-30/submit` open to unauthorized execution. When called, these endpoints invoke `CeisaDocumentService::submit()` synchronously. Because there is no queue mechanism (Obs 1.2.2), long-running or failed DJBC gateway calls block web server workers, creating an availability hazard.

3. **Inability to Test Local Mock Mode without Configuration Tweaks (Obs 1.2.4)**:
   In `CeisaClientService`, the `read_only` guard check executes before the `mock_mode` handler. Since both flags default to `true`, mock POST requests are rejected with HTTP 403 Forbidden unless `CEISA_READ_ONLY` is explicitly disabled in `.env`.

4. **Missing Human Approval Safeguard (Obs 1.3.1, 1.3.2, 1.3.3)**:
   Neither `CeisaDocument` nor `CeisaApiController` enforces or verifies an approval token. An operator or automated system can submit a declaration to CEISA without explicit supervisory authorization, violating customs compliance requirements.

5. **Discrepancy between Database Schema and Application Expectations (Obs 1.5.1, 1.5.2, 1.6.1, 1.6.2)**:
   19 migrations are currently pending in the active database. When `CeisaApiController::handleWebhook` attempts to update `shipments.customs_channel`, MySQL throws error 1054 (`Unknown column 'customs_channel'`), causing the webhook handler to fail with HTTP 500.

6. **Incomplete Model Layer for Operations (Obs 1.5.1, 1.5.2, 1.5.4)**:
   While schemas for `deliveries`, `pod_records`, and `shipment_containers` exist in earlier migrations, no Eloquent models were created for them. Recent features implemented in `2026_11_12_100000` created `inspection_photos` and `shipment_operation_costs` instead of `customs_inspections` and `shipment_deliveries`.

---

## 3. Caveats

1. **Read-Only Scope**: In accordance with the Explorer directive, no source code, configuration files, or database migrations were modified.
2. **Pending Migrations**: The pending migrations in the active database environment prevent certain feature tests (e.g. `CeisaSyncAndCallbackTest`) from passing cleanly. Executing `php artisan migrate` is an implementation step reserved for subsequent phases.
3. **Queue Infrastructure**: Redis or database queue workers were not started during this read-only survey. The observation that queues do not exist is based on the absence of classes in `app/Jobs/` and `.env` configured to `QUEUE_CONNECTION=sync`.

---

## 4. Conclusion

The codebase possesses solid foundations for rule-based document parsing, CEISA payload serialization, and inspection photo storage. However, critical gaps and security exposures must be resolved during Phase 2–4 implementation:

1. **Security Hardening**:
   - Implement dedicated middleware (e.g., `AuthenticateMcpToken` and `EnsureMcpScope`) that rejects unauthenticated requests with HTTP 401 and validates scopes on all `/api/mcp/*` routes.
   - Enforce Sanctum authentication and role permissions on `/api/v1/ceisa/*` submission endpoints.
   - Fix the ordering in `CeisaClientService` so that `mock_mode` executes without being blocked by `read_only`.

2. **CEISA Queue & Resilience**:
   - Create queue jobs (e.g., `SubmitCeisaDeclarationJob`, `PollCeisaStatusJob`) with exponential retry backoff and rate limiting.
   - Enforce a cryptographic Approval Gate Token on `CeisaDocument` before job dispatch.

3. **HS Code Intelligence & Workbench**:
   - Create the `hs_code_override_logs` table, migration, and Eloquent model.
   - Upgrade `CeisaHsCodeService` to return Top-3 recommendations with scores and rationale.
   - Build the two-panel Approve (`V`) / Reject (`X`) UI component.

4. **Field Operations & Model Normalization**:
   - Ensure `ShipmentContainer` Eloquent model is implemented with ISO 6346 validation.
   - Formalize customs inspection and delivery models (`CustomsInspection` / `InspectionPhoto` and `ShipmentDelivery`).
   - Implement reconciliation between `ShipmentOperationCost` and customer invoicing.

5. **Test Coverage**:
   - Run pending migrations on test database.
   - Write comprehensive test suites for MCP authentication, scope enforcement, approval gate token validation, and CEISA mock submissions.

---

## 5. Verification Method

To independently verify the observations in this report:

1. **Verify MCP Authentication Bypass**:
   Execute an unauthenticated JSON-RPC call without an Authorization header:
   ```bash
   curl -X POST "http://localhost/api/mcp/execute" \
        -H "Content-Type: application/json" \
        -d '{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"shipment_list_projects","arguments":{}}}'
   ```
   *Expected Result*: Returns HTTP 200 with data rather than HTTP 401 Unauthorized, proving the authentication bypass.

2. **Verify Missing Middleware in Routes**:
   Inspect `routes/api.php` lines 6-27 to confirm no middleware wraps `/api/v1/ceisa` or `/api/mcp`.

3. **Verify Pending Migrations**:
   Run:
   ```powershell
   php artisan migrate:status
   ```
   *Expected Result*: Migrations from `2026_10_12_000014` to `2026_11_12_100000` display as `Pending`.

4. **Verify Test Failure on Pending Columns**:
   Run:
   ```powershell
   php artisan test --filter=test_webhook_callback_from_ceisa_updates_existing_document_and_shipment
   ```
   *Expected Result*: Fails with `Column not found: 1054 Unknown column 'customs_channel' in 'field list'`.

5. **Verify Missing MCP Tools & Models**:
   Inspect `app/Domain/Mcp/Tools/McpToolRegistry.php` and `app/Models/` to verify absence of `get_document_list`, `compare_hs_code`, `HsCodeOverrideLog`, `CustomsInspection`, and `ShipmentDelivery`.
