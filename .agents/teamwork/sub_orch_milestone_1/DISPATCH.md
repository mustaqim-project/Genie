## 2026-11-12T15:38:41Z
You are the Sub-Orchestrator for Milestone 1 (Foundation, Multi-Container Normalization & Workflow Separation) in the GENIE Export-Import Tracking, Bundle Intelligence, Inspection & CEISA 4.0 Automation project.

Working directory: c:\laragon\www\GENIE
State folder: c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1

MANDATORY FIRST STEPS:
1. Read the original request file at:
   c:\laragon\www\GENIE\.agents\teamwork\ORIGINAL_REQUEST.md
2. Read the master project scope at:
   c:\laragon\www\GENIE\.agents\teamwork\orchestrator\PROJECT.md
3. Read the authoritative architecture blueprint at:
   c:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\618327cc-947d-4ecf-a7cc-65877e9dcc42\09-End-to-End-System-Analysis-and-Architecture.md
4. Read the survey handoff reports:
   c:\laragon\www\GENIE\.agents\teamwork\spec_miner_blueprint_2\handoff.md
   c:\laragon\www\GENIE\.agents\teamwork\explorer_codebase_core_2\handoff.md

Your Scope (Milestone 1 — R1):
1. **Multi-Container Normalization & ISO 6346 Validation**:
   - Migration to ensure `shipment_containers` table has all required columns (`container_number`, `container_size`, `container_type`, `initial_seal_no`, `bc_reseal_no`, `gate_out_at`, `delivered_at`, `eir_file_path`, etc.).
   - Create `App\Models\ShipmentContainer` model with ISO 6346 modulo-11 checksum validation algorithm and `shipment()` relationship.
   - Update `App\Models\Shipment` with `containers()` HasMany relationship, and an Eloquent accessor/mutator for `nomor_kontainer` that maintains 100% backward compatibility for single-string views and legacy data.
2. **Import & Export Workflow State Machines**:
   - Create `App\Services\Workflow\StateMachines\ShipmentStateMachineInterface`.
   - Create `App\Services\Workflow\StateMachines\ImportWorkflowStateMachine` implementing 21 sequential stages (`IMP_01_DOC_RECEIVED` to `IMP_21_COMPLETED`) with branching for Green/Yellow/Red lanes.
   - Create `App\Services\Workflow\StateMachines\ExportWorkflowStateMachine` implementing 17 sequential stages (`EXP_01_INSTRUCTION_RECEIVED` to `EXP_17_COMPLETED`).
   - Integrate the state machines into `ShipmentWorkflowService` with transition validation guards.
3. **Cross-Document Validation Engine Integration (Rules V01–V07)**:
   - Connect `CrossDocumentValidationService` to the primary `Shipment` entity pipeline.
   - Output structured 5-status validation results (`Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review`).
   - Hook validation execution into `ShipmentWorkflowService` lifecycle and verify blocking vs warning behavior.
