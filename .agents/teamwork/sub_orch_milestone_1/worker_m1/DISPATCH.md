## 2026-11-12T17:42:55Z
You are teamwork_worker assigned to implement Milestone 1: Foundation, Multi-Container Normalization & Workflow Separation.

Working directory: c:\laragon\www\GENIE
State folder: c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\worker_m1

MANDATORY FIRST STEPS:
1. Read ORIGINAL_REQUEST.md at:
   c:\laragon\www\GENIE\.agents\teamwork\ORIGINAL_REQUEST.md
2. Read SCOPE.md at:
   c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\SCOPE.md
3. Read PROJECT.md at:
   c:\laragon\www\GENIE\.agents\teamwork\orchestrator\PROJECT.md
4. Read the 3 Explorer handoff reports which contain complete, verified code blueprints:
   - c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\explorer_containers\handoff.md
   - c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\explorer_statemachine\handoff.md
   - c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\explorer_crossval\handoff.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Your Exclusive File Write Ownership:
- database/migrations/2026_11_12_200000_normalize_shipment_containers_table.php
- app/Models/ShipmentContainer.php
- app/Models/Shipment.php (only modify to add container relation/accessors/mutators and state machine hooks)
- app/Services/Workflow/StateMachines/ShipmentStateMachineInterface.php
- app/Services/Workflow/StateMachines/AbstractShipmentStateMachine.php
- app/Services/Workflow/StateMachines/ImportWorkflowStateMachine.php
- app/Services/Workflow/StateMachines/ExportWorkflowStateMachine.php
- app/Services/Shipment/ShipmentWorkflowService.php
- app/Services/Shipment/CrossDocumentValidationService.php
- tests/Unit/ShipmentContainerIso6346Test.php
- tests/Unit/WorkflowStateMachineTest.php
- tests/Unit/CrossDocumentValidationShipmentTest.php

Specific Implementation Tasks:
1. Multi-Container Normalization & ISO 6346
2. Import & Export Workflow State Machines
3. Cross-Document Validation Engine Integration
4. Testing & Verification
