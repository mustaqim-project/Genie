## 2026-11-12T18:19:44Z

Implement the End-to-End Export-Import Document Tracking, Bundle Intelligence, Inspection & CEISA 4.0 Automation system in GENIE across Phase 1 through Phase 4 in accordance with the system analysis and architecture blueprint.

Working directory: c:\laragon\www\GENIE
Integrity mode: development

Reference: c:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\618327cc-947d-4ecf-a7cc-65877e9dcc42\09-End-to-End-System-Analysis-and-Architecture.md

## Requirements

### R1. Foundation, Tracking & Multi-Container Normalization
Normalize multi-container shipments into dedicated records (`shipment_containers`) while maintaining backward compatibility with existing single-string shipment container data. Separate Import (`IMPORT_BC20`) and Export (`EXPORT_BC30`) workflows and lifecycle states into independent state machines, and connect cross-document validation rules (V01–V07) into the primary shipment pipeline.

### R2. Security Hardening & Human Approval Gate
Close authentication and access control vulnerabilities across MCP and CEISA endpoints. Require valid Bearer token credentials and scope checks on all `/api/mcp/*` endpoints, protect CEISA submission endpoints with authentication and permission checks, and enforce an immutable user approval gate token before submitting customs declarations.

### R3. Document Intelligence, Provenance & HS Code Workbench
Implement bundle document extraction with immutable field-level provenance (`document_id`, `page_number`, `bounding_box`, `confidence_score`), asynchronous queue-backed OCR, and the 5-status validation engine (`Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review`). Provide the two-panel HS Code intelligence workbench with explicit Approve (`V`) and Reject (`X`) user decisions recorded in immutable audit logs (`hs_code_override_logs`), along with accuracy evaluation metrics. Expand MCP tools to include document listing, validation, and HS comparison.

### R4. Field Operations, Inspection, Delivery & Financial Control
Implement structured tracking for customs inspections (`customs_inspections`) with multi-stage photo evidence and seal recording (`old_seal_number`, `new_seal_number`), delivery tracking (`shipment_deliveries`) with Proof of Delivery (POD) capture, port operational cost reconciliation, and resilient CEISA API integration with queue retry backoff and rate limiting.

## Verification Resources
- Laravel test suite: `php artisan test`
- CEISA mock mode configuration: `config/ceisa.php` (`mock_mode = true`)

## Acceptance Criteria

### Data Models & Workflow Separation
- [ ] Multi-container shipments store individual container records in `shipment_containers` with ISO 6346 validation while preserving existing shipment data.
- [ ] Import (`IMPORT_BC20`) and Export (`EXPORT_BC30`) transitions follow independent state machine sequences without shared unvalidated state.
- [ ] Cross-document validation rules (V01–V07) execute on shipment bundles and output structured status results (`Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review`).

### Security & CEISA Integration Guardrails
- [ ] All `/api/mcp/*` endpoints reject unauthenticated requests and enforce token scope checks.
- [ ] CEISA submission endpoints (`/api/v1/ceisa/bc-20/submit`, `/bc-30/submit`) require authentication and permissions.
- [ ] CEISA declaration submission fails if user approval has not been explicitly granted and recorded.
- [ ] CEISA API calls operate with queue-based retry backoff and rate limiting.

### Document Extraction & HS Code Intelligence
- [ ] Extracted document fields retain source provenance (`document_id`, `page_number`, `bounding_box`, `confidence_score`) and keep original values untouched when edited.
- [ ] HS Code recommendations provide Top-3 suggestions with rationale, and user decisions (`Approve` / `Reject`) are permanently recorded in `hs_code_override_logs`.
- [ ] MCP suite includes `get_document_list`, `get_inspection_history`, `validate_pib_data`, `validate_peb_data`, `compare_hs_code`, and `generate_compliance_report`.

### Field Operations & Delivery
- [ ] Customs inspections track inspection type, stages, container reference, and old vs. new seal numbers with associated photos.
- [ ] Shipment delivery records capture carrier details, gate-out timestamps, delivery timestamps, and signed Proof of Delivery (POD) files.
- [ ] Operational port and handling costs are recorded per shipment and reconciled with invoicing status.

### Verification Suite
- [ ] All new and modified functionality passes the automated test suite (`php artisan test`) with no regressions.
- [ ] Mock CEISA submission flows verify the approval gate, schema check, and response processing end-to-end.

---
Execution State & Coordination Hand-off:
- State Folder: c:\laragon\www\GENIE\.agents\teamwork\orchestrator_gen2
- Original request path: c:\laragon\www\GENIE\.agents\teamwork\ORIGINAL_REQUEST.md
- Master Blueprint: c:\laragon\www\GENIE\.agents\teamwork\PROJECT.md (contains all 28 features mapped across 5 milestones with interface contracts).
- Prior Survey: Already completed.
- Milestone 1 Sub-Orchestrator state: c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\ (all 3 explorers completed handoffs; worker implementation is in progress).
- E2E Testing Track Orchestrator state: c:\laragon\www\GENIE\.agents\teamwork\sub_orch_e2e_testing\ (TEST_INFRA.md published; worker implementation of Tier 1 tests in progress).
- Your role: Project Orchestrator Gen 2. Manage the sub-orchestrators, track milestone completions, and drive end-to-end completion.
