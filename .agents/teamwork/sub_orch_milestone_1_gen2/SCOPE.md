# Scope: Milestone 1 — Foundation, Multi-Container Normalization & Workflow Separation

## Architecture
- **Framework**: Laravel 10 / PHP 8.2 with MySQL (`PATS_CEISA`), Eloquent ORM.
- **Goal**:
  1. Relational multi-container management with ISO 6346 modulo-11 checksum validation and 100% backward-compatible accessor on `Shipment`.
  2. Decoupled Import (`IMPORT_BC20`, 21 sequential stages with lane branching) and Export (`EXPORT_BC30`, 17 sequential stages) state machines.
  3. Integrated Cross-Document Validation Engine (Rules V01–V07) connected to primary `Shipment` entity pipeline returning 5-status results.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Multi-Container Normalization | Relational container tracking via `shipment_containers` with backward-compatible single-string accessor on `Shipment` | M1 | Blueprint §3.2, R1 |
| 2 | ISO 6346 Modulo-11 Checksum | Strict container check digit algorithm verification (modulo-11) | M1 | Blueprint §3.2, R1 |
| 3 | Import State Machine (`IMPORT_BC20`) | 21-stage sequential lifecycle from doc receipt to financial close with branching on customs channels | M1 | Blueprint §2.2, R1 |
| 4 | Export State Machine (`EXPORT_BC30`) | 17-stage sequential lifecycle from SI to export invoicing with stuffing and NPE/PPBE tracking | M1 | Blueprint §2.3, R1 |
| 5 | Cross-Document Validation Engine (V01–V07) | Inter-document consistency checker (V01–V07) connected to primary shipment pipeline emitting 5-status results | M1 | Blueprint §3.3, R1 |
| 6 | 5-Status Validation Categorization | Categorization engine emitting `Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review` | M1 | Blueprint §3.3, R1 |

## Implementation Items
1. **Database Migration & Schema Alignment**:
   - Inspect existing `shipment_containers` table and create migration to add missing columns:
     - `container_number` VARCHAR(20) NOT NULL (or map existing `number`),
     - `container_size` ENUM('20FT', '40FT', '40HC', '45FT', 'LCL') NOT NULL DEFAULT '40HC',
     - `container_type` ENUM('GP', 'RF', 'OT', 'FR', 'TK') NOT NULL DEFAULT 'GP',
     - `initial_seal_no` VARCHAR(60) NULL,
     - `bc_reseal_no` VARCHAR(60) NULL,
     - `gate_out_at` DATETIME NULL,
     - `delivered_at` DATETIME NULL,
     - `eir_file_path` VARCHAR(255) NULL.
2. **Eloquent Models**:
   - `App\Models\ShipmentContainer`:
     - Has UUID primary key, fillable fields, `shipment()` BelongsTo relationship.
     - Implements `validateIso6346(string $containerNumber): bool` with strict modulo-11 logic and letter weighting.
   - Update `App\Models\Shipment`:
     - `containers()` HasMany relationship.
     - `nomor_kontainer` accessor and mutator preserving 100% backward compatibility for single-string views.
3. **Workflow State Machines**:
   - `App\Services\Workflow\StateMachines\ShipmentStateMachineInterface`:
     - Contract declaring: `getCurrentStage()`, `getAllStages()`, `canTransitionTo(string $targetStage): bool`, `transitionTo(string $targetStage, array $context = []): bool`, `getAvailableTransitions(): array`, `getStageLabel(string $stage): string`.
   - `App\Services\Workflow\StateMachines\ImportWorkflowStateMachine`:
     - 21 sequential stages (`IMP_01_DOC_RECEIVED` to `IMP_21_COMPLETED`).
     - Channel branching at `IMP_12_CHANNEL_ROUTED`: Green Lane (`SPPB`) -> `IMP_17`, Yellow Lane (`NPD`/`DNP`) -> `IMP_13`, Red Lane (`SPJM`) -> `IMP_14`.
   - `App\Services\Workflow\StateMachines\ExportWorkflowStateMachine`:
     - 17 sequential stages (`EXP_01_INSTRUCTION_RECEIVED` to `EXP_17_COMPLETED`).
     - Branching at `EXP_09_PEB_RESPONSE`: NPE -> `EXP_10` -> `EXP_14`, PPBE -> `EXP_12_CUSTOMS_EXAM` -> `EXP_13`.
   - Update `ShipmentWorkflowService`:
     - Factory method `getStateMachine(Shipment $shipment): ShipmentStateMachineInterface`.
     - Guard stage transitions using the appropriate state machine.
4. **Cross-Document Validation Engine Integration**:
   - Update `App\Services\Shipment\CrossDocumentValidationService`:
     - Add `validateShipment(Shipment $shipment): array` method.
     - Evaluate rules V01 (BL Consistency), V02 (Weight Tolerance), V03 (Invoice Items Sum), V04 (Container Checksum Modulo-11), V05 (HS Code BTKI), V06 (Currency & KMK), V07 (NPWP 16-Digit).
     - Return structured results with 5-status categories: `Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review`.
     - Expose helper `isSubmissionBlocked(array $validationResults): bool`.
   - Hook into `ShipmentWorkflowService`.
5. **Comprehensive Unit & Integration Tests**:
   - `tests/Unit/ShipmentContainerIso6346Test.php` testing valid/invalid ISO 6346 numbers, modulo-11 arithmetic, remainder 10 cases.
   - `tests/Unit/WorkflowStateMachineTest.php` testing Import 21-stage and Export 17-stage valid and invalid transitions, channel branching.
   - `tests/Unit/CrossDocumentValidationShipmentTest.php` testing rules V01–V07 on `Shipment` models and 5-status outputs.

## Interface Contracts
- `Shipment::containers()` returns `HasMany<ShipmentContainer>`.
- `ShipmentContainer::validateIso6346(string $containerNumber): bool` validates container number check digit.
- `Shipment::getStateMachine(): ShipmentStateMachineInterface` returns configured state machine.
- `CrossDocumentValidationService::validateShipment(Shipment $shipment): array` returns:
  ```php
  [
      'is_blocked' => bool,
      'summary' => ['valid' => int, 'warning' => int, 'mismatch' => int, 'missing_data' => int, 'manual_review' => int],
      'rules' => [
          [
              'rule_code' => 'V01',
              'rule_name' => 'B/L Consistency',
              'severity' => 'BLOCKING',
              'status' => 'Valid'|'Warning'|'Mismatch'|'Missing Data'|'Requires Manual Review',
              'is_passed' => bool,
              'message' => string,
              'details' => array
          ],
          ...
      ]
  ]
  ```

## Code Layout
- `app/Models/ShipmentContainer.php`
- `app/Models/Shipment.php` (update with `containers()` & accessor/mutator)
- `database/migrations/2026_11_12_200000_normalize_shipment_containers_table.php`
- `app/Services/Workflow/StateMachines/ShipmentStateMachineInterface.php`
- `app/Services/Workflow/StateMachines/ImportWorkflowStateMachine.php`
- `app/Services/Workflow/StateMachines/ExportWorkflowStateMachine.php`
- `app/Services/Shipment/ShipmentWorkflowService.php` (integrate state machines)
- `app/Services/Shipment/CrossDocumentValidationService.php` (add `validateShipment`)
- `tests/Unit/ShipmentContainerIso6346Test.php`
- `tests/Unit/WorkflowStateMachineTest.php`
- `tests/Unit/CrossDocumentValidationShipmentTest.php`
