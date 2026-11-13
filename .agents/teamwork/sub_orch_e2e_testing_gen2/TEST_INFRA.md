# E2E Test Infra: GENIE Export-Import Tracking & CEISA 4.0 Automation

## Test Philosophy
- **Opaque-Box & Requirement-Driven**: Tests are designed directly from `ORIGINAL_REQUEST.md` and `09-End-to-End-System-Analysis-and-Architecture.md`, independent of internal implementation nuances.
- **4-Tier Test Methodology**:
  - **Tier 1 — Feature Coverage**: Comprehensive isolated happy-path tests (>=5 test cases per feature).
  - **Tier 2 — Boundary & Corner Cases**: Edge conditions, invalid inputs, tolerances, and negative scenarios (>=5 test cases per feature).
  - **Tier 3 — Cross-Feature Combinations**: Pairwise and multi-module interaction verification.
  - **Tier 4 — Real-World Application Scenarios**: Full end-to-end operational lifecycles (Import BC 2.0 Green/Red Lane, Export BC 3.0, Delivery & Financial Close).
- **Mock Safety**: External integrations (DJBC CEISA OpenAPI gateway, Tesseract OCR, external LLM calls) are mocked or run in mock mode (`config/ceisa.php` with `mock_mode = true`).

---

## Directory Layout
```
tests/E2E/
├── BaseE2ETestCase.php                         # Base test case with test helpers, factories, and mocks
├── Tier1_Feature/                              # Tier 1: Feature Coverage (>=5 test cases per feature)
│   ├── ContainerAndChecksumTier1Test.php       # Features 1 & 2
│   ├── WorkflowStateMachineTier1Test.php       # Features 3 & 4
│   ├── CrossDocumentValidationTier1Test.php   # Features 5 & 6
│   ├── SecurityAndApprovalGateTier1Test.php   # Features 7, 8, 9, 10
│   ├── DocumentIntelligenceAndHsTier1Test.php  # Features 11, 12, 13, 14, 15, 16
│   ├── McpToolsTier1Test.php                   # Features 17, 18, 19, 20, 21, 22
│   └── FieldOpsAndDeliveryTier1Test.php        # Features 23, 24, 25, 26, 27
├── Tier2_Boundary/                             # Tier 2: Boundary & Corner Cases
│   ├── ContainerIso6346BoundaryTest.php        # ISO 6346 checksum edge cases, length, formatting
│   ├── WorkflowTransitionBoundaryTest.php      # Invalid stage skips, unauthorized transitions
│   ├── ValidationToleranceBoundaryTest.php    # Weight tolerance limits (0.5%), missing docs
│   ├── SecurityAuthBoundaryTest.php            # Expired tokens, missing scopes, tampered hashes
│   ├── HsCodeAndProvenanceBoundaryTest.php     # Low OCR confidence (<0.80), generic terms
│   └── InspectionAndCostBoundaryTest.php       # Photo stage validation, cost reconciliation edges
├── Tier3_CrossFeature/                         # Tier 3: Cross-Feature Interactions
│   ├── MultiContainerValidationIntegrationTest.php # Multi-container + V01-V07 cross-doc checks
│   ├── ApprovalGateAndCeisaSubmissionTest.php  # Approval token + payload SHA-256 + CEISA mock
│   ├── InspectionPhotoAndResealTest.php        # SPJM ticket + 5-stage photos + seal change
│   └── McpCustomsAgentWorkflowTest.php         # Bearer token auth + document list + validation tools
└── Tier4_Scenarios/                            # Tier 4: Real-World Operational Scenarios
    ├── ImportBc20GreenLaneScenarioTest.php     # 21-stage import flow (Jalur Hijau -> SPPB)
    ├── ImportBc20RedLaneInspectionScenarioTest.php # Red lane flow (SPJM -> Behandle -> Reseal -> SPPB)
    ├── ExportBc30CompleteScenarioTest.php      # 17-stage export flow (SI -> Draft PEB -> NPE -> Stuffing)
    └── DeliveryAndPortCostReconciliationTest.php # Delivery gate-out, POD capture, cost settlement
```

---

## Feature Coverage Matrix

| # | Feature | Tier 1 | Tier 2 | Tier 3 | Tier 4 |
|---|---------|:------:|:------:|:------:|:------:|
| 1 | Multi-Container Normalization | 5 | 5 | ✓ | ✓ |
| 2 | ISO 6346 Modulo-11 Checksum | 5 | 5 | ✓ | ✓ |
| 3 | Import State Machine (`IMPORT_BC20`) | 5 | 5 | ✓ | ✓ |
| 4 | Export State Machine (`EXPORT_BC30`) | 5 | 5 | ✓ | ✓ |
| 5 | Cross-Document Validation Engine (V01–V07) | 5 | 5 | ✓ | ✓ |
| 6 | 5-Status Validation Categorization | 5 | 5 | ✓ | ✓ |
| 7 | MCP Bearer Token Auth & Granular Scopes | 5 | 5 | ✓ | - |
| 8 | CEISA Endpoint Protection & RBAC | 5 | 5 | ✓ | ✓ |
| 9 | Immutable Human Approval Gate Token | 5 | 5 | ✓ | ✓ |
| 10 | CEISA Mock Mode Precedence Fix | 5 | 5 | ✓ | ✓ |
| 11 | Field-Level Provenance Retention | 5 | 5 | ✓ | ✓ |
| 12 | Non-Destructive Editing Protocol | 5 | 5 | - | ✓ |
| 13 | Asynchronous Queue-Backed OCR | 5 | 5 | - | ✓ |
| 14 | HS Code Intelligence Workbench (2-Panel UI) | 5 | 5 | - | ✓ |
| 15 | Explicit Approve (`V`) / Reject (`X`) Decisions | 5 | 5 | - | ✓ |
| 16 | HS Code Empirical Accuracy Protocol | 5 | 5 | - | - |
| 17 | MCP Tool: `get_document_list` | 5 | 5 | ✓ | - |
| 18 | MCP Tool: `get_inspection_history` | 5 | 5 | ✓ | - |
| 19 | MCP Tool: `validate_pib_data` | 5 | 5 | ✓ | - |
| 20 | MCP Tool: `validate_peb_data` | 5 | 5 | ✓ | - |
| 21 | MCP Tool: `compare_hs_code` | 5 | 5 | ✓ | - |
| 22 | MCP Tool: `generate_compliance_report` | 5 | 5 | ✓ | - |
| 23 | Customs Inspections Management | 5 | 5 | ✓ | ✓ |
| 24 | Multi-Stage Photo Evidence & Seal Tracking | 5 | 5 | ✓ | ✓ |
| 25 | Delivery Tracking & Proof of Delivery (POD) | 5 | 5 | ✓ | ✓ |
| 26 | Port Operational Cost Reconciliation | 5 | 5 | ✓ | ✓ |
| 27 | Resilient CEISA Client & Queue Backoff | 5 | 5 | ✓ | ✓ |
| 28 | Comprehensive E2E Verification & Hardening | 5 | 5 | ✓ | ✓ |

---

## Test Execution Command
Full suite:
```bash
php artisan test tests/E2E
```
Tier-specific runs:
```bash
php artisan test tests/E2E/Tier1_Feature
php artisan test tests/E2E/Tier2_Boundary
php artisan test tests/E2E/Tier3_CrossFeature
php artisan test tests/E2E/Tier4_Scenarios
```
