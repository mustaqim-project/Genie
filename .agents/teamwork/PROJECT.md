# Project: GENIE Export-Import Tracking, Bundle Intelligence, Inspection & CEISA 4.0 Automation

## Architecture
- **Framework**: Laravel 10 / PHP 8.2 with MySQL (`PATS_CEISA`), Eloquent ORM, Blade UI & Alpine.js, Sanctum authentication, MCP (Model Context Protocol) JSON-RPC API, and DJBC CEISA 4.0 Host-to-Host (H2H) integration.
- **Dual-Track Workflow**:
  - **Track 1: Implementation Track**:
    - Milestone 1: Foundation, Multi-Container Normalization & Workflow Separation (R1)
    - Milestone 2: Security Hardening, Endpoint Protection & Human Approval Gate (R2)
    - Milestone 3: Document Intelligence, Field Provenance & HS Code Workbench (R3)
    - Milestone 4: Field Operations, Customs Inspections, Deliveries & Port Financials (R4)
    - Final Milestone: E2E Test Suite Pass (Tiers 1–4) & Adversarial Coverage Hardening (Tier 5)
  - **Track 2: E2E Testing Track**:
    - Requirement-driven, opaque-box test infrastructure and comprehensive multi-tier test cases (Tiers 1–4), concluding with `TEST_READY.md`.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Multi-Container Normalization | Relational container tracking via `shipment_containers` with backward-compatible single-string accessor on `Shipment` | M1 | Blueprint §3.2, R1 |
| 2 | ISO 6346 Modulo-11 Checksum | Strict container check digit algorithm verification (modulo-11) | M1 | Blueprint §3.2, R1 |
| 3 | Import State Machine (`IMPORT_BC20`) | 21-stage sequential lifecycle from doc receipt to financial close with branching on customs channels | M1 | Blueprint §2.2, R1 |
| 4 | Export State Machine (`EXPORT_BC30`) | 17-stage sequential lifecycle from SI to export invoicing with stuffing and NPE/PPBE tracking | M1 | Blueprint §2.3, R1 |
| 5 | Cross-Document Validation Engine (V01–V07) | Inter-document consistency checker (V01–V07) connected to primary shipment pipeline emitting 5-status results | M1 | Blueprint §3.3, R1 |
| 6 | 5-Status Validation Categorization | Categorization engine emitting `Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review` | M1 | Blueprint §3.3, R1 |
| 7 | MCP Bearer Token Auth & Granular Scopes | Token authentication on `/api/mcp/*` with `McpCredential` hash verification and scope checks (`read`, `analyze`, `write`) | M2 | Blueprint §3.6, R2 |
| 8 | CEISA Endpoint Protection & RBAC | Sanctum authentication and `ceisa.submit` permission enforcement on `/api/v1/ceisa/*` submission endpoints | M2 | Blueprint §3.6, R2 |
| 9 | Immutable Human Approval Gate Token | Cryptographic supervisor sign-off token and SHA-256 payload lock on `CeisaDocument` before submission | M2 | Blueprint §3.5, R2 |
| 10 | CEISA Mock Mode Precedence Fix | Correction in `CeisaClientService` to allow mock responses when `mock_mode = true` without being blocked by `read_only` | M2 | Blueprint §3.5, R2 |
| 11 | Field-Level Provenance Retention | Extraction schema recording `document_id`, `page_number`, `bounding_box`, and `confidence_score` in `import_fields` | M3 | Blueprint §3.3, R3 |
| 12 | Non-Destructive Editing Protocol | Preserving `original_value` while capturing `corrected_value` and operator identity during manual edits | M3 | Blueprint §3.3, R3 |
| 13 | Asynchronous Queue-Backed OCR | Queued background job pipeline for image OCR and document text parsing with duplicate detection | M3 | Blueprint §3.3, R3 |
| 14 | HS Code Intelligence Workbench (2-Panel UI) | Split-view workbench: Final declaration form (left) vs Top-3 BTKI / AI recommendations with rationale (right) | M3 | Blueprint §3.4, R3 |
| 15 | Explicit Approve (`V`) / Reject (`X`) Decisions | User decision capture recording acceptance or rejection into immutable audit trail `hs_code_override_logs` | M3 | Blueprint §3.4, R3 |
| 16 | HS Code Empirical Accuracy Protocol | Benchmark evaluation suite computing Top-1 (>=90%) and Top-3 (>=96%) accuracy against gold-standard dataset | M3 | Blueprint §3.4, R3 |
| 17 | MCP Tool: `get_document_list` | Tool retrieving document bundle checklist, upload status, and missing items | M3 | Blueprint §3.6, R3 |
| 18 | MCP Tool: `get_inspection_history` | Tool retrieving inspection records, SPJM tickets, seal numbers, and photo metadata | M3 | Blueprint §3.6, R3 |
| 19 | MCP Tool: `validate_pib_data` | Tool executing cross-document and CEISA schema pre-checks for BC 2.0 (PIB) | M3 | Blueprint §3.6, R3 |
| 20 | MCP Tool: `validate_peb_data` | Tool executing cross-document and export readiness checks for BC 3.0 (PEB) | M3 | Blueprint §3.6, R3 |
| 21 | MCP Tool: `compare_hs_code` | Tool comparing candidate BTKI codes, duties (BM, PPN, PPh, BK), and Lartas restrictions | M3 | Blueprint §3.6, R3 |
| 22 | MCP Tool: `generate_compliance_report` | Tool synthesizing full shipment compliance posture, risk score, and free-time SLA | M3 | Blueprint §3.6, R3 |
| 23 | Customs Inspections Management (`customs_inspections`) | Relational model tracking inspection type (SPJM/NPD/PPBE), TPS location, inspector, tickets, and official report (BA) | M4 | Blueprint §3.2, R4 |
| 24 | Multi-Stage Photo Evidence & Seal Tracking | Timestamped/GPS photo capture across 5 inspection / 4 stuffing stages; tracking `old_seal_number` vs `new_seal_number` | M4 | Blueprint §3.2, R4 |
| 25 | Delivery Tracking & Proof of Delivery (`shipment_deliveries`) | Structured delivery tracking: carrier, driver, armada plate, gate-out timestamp, arrival, and signed POD upload | M4 | Blueprint §3.2, R4 |
| 26 | Port Operational Cost Reconciliation | Recording operational disbursements (`DO`, `Extend DO`, `BHDL`, `LOLO`, `Trucking`) and reconciling with invoicing status | M4 | Blueprint §1.1, R4 |
| 27 | Resilient CEISA Client & Queue Backoff | Asynchronous queue jobs with exponential retry backoff (10s, 30s, 120s) and rate limiting for CEISA gateway calls | M4 | Blueprint §3.5, R4 |
| 28 | Comprehensive E2E Verification & Hardening | Complete requirement-driven opaque-box test suite (Tiers 1–4) & adversarial white-box coverage hardening (Tier 5) | M5 | Blueprint §5.1, AC |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| E2E | E2E Testing Track | Requirement-driven opaque-box test suite (Tiers 1–4), test harness, runner, and `TEST_READY.md` publication | None | IN_PROGRESS |
| M1 | Foundation, Containers & Workflow Separation | `shipment_containers` model & migration, ISO 6346 validation, `ImportWorkflowStateMachine` (21 stages), `ExportWorkflowStateMachine` (17 stages), V01–V07 validation engine pipeline integration | None | IN_PROGRESS |
| M2 | Security Hardening & Approval Gate | MCP Bearer token authentication & scope enforcement, CEISA endpoint auth & permissions (`ceisa.submit`), immutable human approval gate token on `ceisa_documents`, CEISA mock mode precedence fix | M1 interface contracts | PLANNED |
| M3 | Document Intelligence & HS Code Workbench | Field provenance retention (`document_id`, `page_number`, `bounding_box`, `confidence_score`), non-destructive editing, queued OCR job, 2-panel HS workbench, `hs_code_override_logs`, accuracy metrics, 6 new MCP tools | M1, M2 | PLANNED |
| M4 | Field Operations, Delivery & Financial Control | `customs_inspections` model & multi-stage photos, old vs new seal comparison, `shipment_deliveries` model & POD, port operational cost reconciliation, resilient CEISA queue retry backoff & rate limiting | M1, M2 | PLANNED |
| M5 | Final E2E Suite Pass & Coverage Hardening | Phase 1: Pass 100% of E2E tests (Tiers 1–4). Phase 2: Adversarial coverage hardening (Tier 5) via Challenger-Worker-Reviewer loop | E2E (`TEST_READY.md`), M1, M2, M3, M4 | PLANNED |

## Interface Contracts

### M1 (Containers & Workflows) ↔ M2 (Security & Approval)
- `Shipment::containers()` returns `HasMany<ShipmentContainer>`.
- `ShipmentContainer::validateIso6346(string $containerNumber): bool` calculates modulo-11 check digit.
- `Shipment::getWorkflowStateMachine()` returns `ImportWorkflowStateMachine` or `ExportWorkflowStateMachine` implementing `ShipmentStateMachineInterface`.
- `CrossDocumentValidationService::validateShipment(Shipment $shipment): array` returns array of rule results with keys: `['rule_code', 'rule_name', 'severity', 'status', 'is_passed', 'message', 'details']`.

### M2 (Security & Approval) ↔ M3 (Intelligence) & M4 (Operations / CEISA)
- `CeisaDocument` attributes: `approved_by_user_id`, `approved_at`, `approval_payload_hash`, `approval_token`.
- `CeisaDocumentService::approveDeclaration(CeisaDocument $doc, int $userId): string` generates approval token and records hash.
- `CeisaDocumentService::submit(CeisaDocument $doc)` throws `DomainException` if unapproved or if payload hash mismatches approval hash.
- Middleware `AuthenticateMcpToken` and `EnsureMcpScope` guard `/api/mcp/*`.
- Middleware `auth:sanctum` and `permission:ceisa.submit` guard `/api/v1/ceisa/bc-20/submit` and `/api/v1/ceisa/bc-30/submit`.

### M3 (Intelligence & HS Workbench) ↔ M4 (Operations & CEISA)
- `HsCodeOverrideLog` schema: `id`, `shipment_id`, `shipment_item_id`, `original_ai_hs_code`, `original_confidence`, `selected_hs_code`, `decision` (ENUM: `APPROVED`, `REJECTED`), `override_reason`, `user_id`, timestamps.
- `McpToolRegistry` registers 6 new tools implementing `McpToolInterface`.

### M4 (Field Operations) ↔ M1 (Shipment)
- `CustomsInspection` schema: `id`, `shipment_id`, `container_id`, `inspection_type`, `ticket_number`, `scheduled_at`, `location_tps`, `inspector_name`, `old_seal_number`, `new_seal_number`, `ba_file_path`, `status`.
- `ShipmentDelivery` schema: `id`, `shipment_id`, `container_id`, `trucking_vendor_name`, `driver_name`, `armada_plate_number`, `gate_out_at`, `arrived_at`, `signed_pod_file_path`, `status`.

## Code Layout
- Models: `app/Models/` (`ShipmentContainer.php`, `CustomsInspection.php`, `ShipmentDelivery.php`, `HsCodeOverrideLog.php`)
- State Machines: `app/Services/Workflow/StateMachines/` (`ImportWorkflowStateMachine.php`, `ExportWorkflowStateMachine.php`, `ShipmentStateMachineInterface.php`)
- Validations: `app/Services/Shipment/CrossDocumentValidationService.php`
- Security Middleware: `app/Http/Middleware/` (`AuthenticateMcpToken.php`, `EnsureMcpScope.php`)
- Jobs: `app/Jobs/` (`ProcessDocumentOcrJob.php`, `SubmitCeisaDeclarationJob.php`, `PollCeisaStatusJob.php`)
- MCP Tools: `app/Domain/Mcp/Tools/` (`GetDocumentListTool.php`, `GetInspectionHistoryTool.php`, `ValidatePibDataTool.php`, `ValidatePebDataTool.php`, `CompareHsCodeTool.php`, `GenerateComplianceReportTool.php`)
- Controllers: `app/Http/Controllers/Api/` (`McpCustomsAgentController.php`, `CeisaApiController.php`)
- Migrations: `database/migrations/`
- Tests: `tests/Unit/`, `tests/Feature/`, `tests/E2E/`
