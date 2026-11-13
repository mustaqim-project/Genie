# Handoff Report: Cross-Document Validation Engine Integration (Rules V01–V07)

- **Milestone**: Milestone 1 — Foundation, Multi-Container Normalization & Workflow Separation
- **Component**: Cross-Document Validation Engine Integration (Rules V01–V07) & 5-Status Categorization Engine
- **Investigator**: Teamwork Explorer (`sub_orch_milestone_1/explorer_crossval`)
- **Date**: 2026-11-12T17:30:00Z
- **Target Output File**: `c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\explorer_crossval\handoff.md`

---

## 1. Observation

### 1.1 Existing Validation Service Implementation (`CrossDocumentValidationService.php`)
Direct inspection of `app/Services/Shipment/CrossDocumentValidationService.php` reveals:

1. **Strict Coupling to `ImportDocument`**:
   - Lines 14–17:
     ```php
     public function validateDocument(ImportDocument $document): array
     {
         $fields = $document->fields()->pluck('corrected_value', 'field_name')->toArray();
         $batchId = $document->file ? $document->file->batch_id : null;
     ```
   - The engine only accepts `ImportDocument $document`. It never interacts directly with `App\Models\Shipment`.
   - Field data is extracted strictly from `$document->fields()` (table `import_fields`), ignoring `Shipment`, `ShipmentItem`, `ShipmentContainer`, and `Client`.

2. **Inconsistent Status Terminology**:
   - The current rules return arbitrary strings: `'PASSED'`, `'FAILED'`, and occasionally `'WARNING'`.
     - `ruleV01BlConsistency` (Lines 70, 90, 102): Returns status `'WARNING'`, `'FAILED'`, or `'PASSED'`.
     - `ruleV02WeightTolerance` (Lines 118, 128): Returns `'FAILED'` or `'PASSED'`.
     - `ruleV03InvoiceItemsSum` (Lines 146, 156): Returns `'FAILED'` or `'PASSED'`.
     - `ruleV04ContainerChecksum` (Lines 171, 183, 193): Returns `'PASSED'` or `'FAILED'`.
     - `ruleV05HsCodeBtki` (Lines 216, 226): Returns `'FAILED'` or `'PASSED'`.
     - `ruleV06CurrencyAndKmk` (Lines 242, 252): Returns `'FAILED'` or `'PASSED'`.
     - `ruleV07Npwp16Digit` (Lines 267, 277): Returns `'FAILED'` or `'PASSED'`.
   - None of the rules output the mandated 5-status categories: `Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review`.

3. **Current Service Usage Across the Codebase**:
   - Running `Get-ChildItem -Path app -Recurse -Filter *.php | Select-String "CrossDocumentValidationService"` reveals that `CrossDocumentValidationService` is called in only **one** location:
     - `app/Services/Shipment/BatchImportService.php:250`:
       ```php
       protected function validateDocumentRules(ImportDocument $doc): void
       {
           $validator = app(CrossDocumentValidationService::class);
           $validator->validateDocument($doc);
       }
       ```
   - It is completely absent from `app/Services/Shipment/ShipmentWorkflowService.php`, `app/Http/Controllers/BC20Controller.php`, `app/Http/Controllers/BC30Controller.php`, and `app/Http/Controllers/Api/CeisaApiController.php`.
   - There are **zero** existing automated tests in `tests/` referencing `CrossDocumentValidationService`.

4. **Analysis of Individual Existing Rules**:
   - **`ruleV01BlConsistency` (Lines 62–106)**:
     - Monitored fields: `bl_number`, `bill_of_lading`.
     - Severity: `BLOCKING`.
     - Logic: Compares B/L number between the given document and other documents in the same `batch_id`. If B/L is missing, returns status `'WARNING'`. If different, returns `'FAILED'`.
   - **`ruleV02WeightTolerance` (Lines 108–132)**:
     - Monitored fields: `gross_weight`, `net_weight`.
     - Severity: `WARNING`.
     - Logic: Checks if `net_weight > gross_weight`. If so, returns `'FAILED'`. Does not compute cross-document weight variance or packing list vs B/L manifest tolerance (<= 0.5%).
   - **`ruleV03InvoiceItemsSum` (Lines 134–161)**:
     - Monitored fields: `total_amount`, `invoice_total`, line items CIF/FOB sum.
     - Severity: `WARNING`.
     - Logic: Compares header total vs sum of line items. Allows $1.00 rounding tolerance (`abs($headerTotal - $itemsTotal) > 1.0`).
   - **`ruleV04ContainerChecksum` (Lines 163–197)**:
     - Monitored fields: `container_number`.
     - Severity: `WARNING`.
     - Logic: Currently performs only a regex match: `preg_match('/^[A-Z]{4}[0-9]{7}$/', $containerNo)`.
     - **Deficiency**: Does **not** perform ISO 6346 Modulo-11 check digit arithmetic. A malformed check digit (e.g. `OOLU9081726` where actual check digit is 6 but modulo-11 demands 0) erroneously passes!
   - **`ruleV05HsCodeBtki` (Lines 199–230)**:
     - Monitored fields: `$doc->items->hs_code_proposed`.
     - Severity: `BLOCKING`.
     - Logic: Strips non-digits; checks `strlen($hs) !== 8`. Does not verify existence in the BTKI 2022 tariff table (`hs_codes`).
   - **`ruleV06CurrencyAndKmk` (Lines 232–256)**:
     - Monitored fields: `currency`.
     - Severity: `BLOCKING`.
     - Logic: Checks against hardcoded array `['USD', 'EUR', 'CNY', 'SGD', 'JPY', 'IDR', 'GBP', 'AUD', 'MYR']`. Does not verify active KMK exchange rate availability via `CeisaKursService`.
   - **`ruleV07Npwp16Digit` (Lines 258–281)**:
     - Monitored fields: `importer_npwp`, `client_npwp`.
     - Severity: `BLOCKING`.
     - Logic: Strips non-digits; checks if length is 15 or 16 digits.

### 1.2 Entity Model & Relationship Observations
1. **`App\Models\Shipment` (`app/Models/Shipment.php`)**:
   - Key attributes:
     - `nomor_bl_awb`, `tgl_bl_awb`
     - `total_bruto`, `total_netto`
     - `kode_valuta`, `total_nilai_valuta`, `ndbm_kurs`, `total_nilai_idr`
     - `nomor_kontainer`, `total_kontainer`
     - `client_id` (foreign key to `clients`)
     - `status_tahap` (VARCHAR 50)
   - Relationships:
     - `client()` -> `BelongsTo<Client>`
     - `items()` -> `HasMany<ShipmentItem>`
     - `documents()` -> `HasMany<ShipmentDocument>` (where `is_current = true`)
     - `allDocuments()` -> `HasMany<ShipmentDocument>`
     - `containers()` -> Designed by `explorer_containers` as `HasMany<ShipmentContainer>`
   - Existing partial check: `getThreeWayMatchingAttribute()` (lines 433–476) computes crude matching but is unlinked from the formal V01–V07 ruleset.

2. **`App\Models\ShipmentItem` (`app/Models/ShipmentItem.php`)**:
   - Key attributes:
     - `kode_hs` (VARCHAR, e.g. `'8504.40.90'` or `'85044090'`)
     - `uraian_barang` (TEXT)
     - `hs_confidence_score` (DECIMAL 2, e.g. `0.95` or `0.65`)
     - `nilai_fob` (DECIMAL 2)
     - `berat_netto`, `berat_bruto` (DECIMAL 2)
     - `jumlah_kemasan`, `jenis_kemasan`

3. **`App\Models\Client` (`app/Models/Client.php`)**:
   - Key attributes:
     - `npwp` (VARCHAR, e.g. `'01.234.567.8-901.000'` or `'0123456789012345'`)
     - `nib` (VARCHAR)
     - `name` (VARCHAR)

4. **`App\Models\ShipmentContainer` (`app/Models/ShipmentContainer.php`)**:
   - Designed by peer explorer (`explorer_containers`) with:
     - `container_number` (VARCHAR 20)
     - `validateIso6346(string $containerNumber): bool` (Modulo-11 algorithm)
     - `calculateCheckDigit(string $first10Chars): ?int`

5. **`App\Services\Ceisa\CeisaHsCodeService` (`app/Services/Ceisa/CeisaHsCodeService.php`)**:
   - Line 710: `getTariffAndLartas(string $hsCode): array`
     - Queries local `hs_codes` table and `builtInKnowledgeBase`.
     - Returns tariff breakdown (BM, PPN, PPH, BK) and Lartas requirement flags.

6. **`App\Services\Ceisa\CeisaKursService` (`app/Services/Ceisa/CeisaKursService.php`)**:
   - Line 23: `getActiveKurs(string $kodeValuta = 'USD', ?string $targetDate = null, bool $forceRefresh = false): array`
     - Verifies currency against active KMK tax exchange rates via Cache -> DB (`ceisa_exchange_rates`) -> CEISA OpenAPI -> Fallback.

---

## 2. Logic Chain

### 2.1 The 5-Status Categorization Architecture
The system requirements (R1, R3, SCOPE.md §4.3) mandate standardizing all validation outcomes into five discrete, immutable statuses:

| Status | Code / Badge | Definition | System Action |
|---|:---:|---|---|
| **`Valid`** | Green | The rule passes completely. Data matches within configured numeric/string tolerances. | Allows progress; does not block submission. |
| **`Warning`** | Yellow | Minor non-fatal discrepancy (e.g. weight discrepancy within 0.5% tolerance, 15-digit legacy NPWP). | Informs operator; does **not** block CEISA submission. |
| **`Mismatch`** | Red | Serious logical or data contradiction (e.g. conflicting B/L numbers, Net > Gross weight, invoice sum difference > $1.00, invalid container check digit, invalid HS length). | If rule severity is `BLOCKING`, **blocks CEISA submission**. If severity is `WARNING`, flags alert. |
| **`Missing Data`** | Gray | Mandatory fields required by customs declaration or cross-matching are absent, null, or zero. | If rule severity is `BLOCKING`, **blocks CEISA submission**. |
| **`Requires Manual Review`** | Purple | Extraction confidence is low (`hs_confidence_score < 0.80`), OCR text was ambiguous, or commodity description requires human supervisor review. | If rule severity is `BLOCKING`, **blocks CEISA submission** until supervisor explicitly approves. |

### 2.2 Rule-by-Rule Adaptation for `Shipment` Entity

#### Rule V01: B/L Consistency (`ruleV01BlConsistency`)
- **Severity**: `BLOCKING`
- **Rule Code**: `V01`
- **Monitored Entity**: `Shipment $shipment`
- **Data Inputs**:
  - `$shipment->nomor_bl_awb`
  - Associated document files in `$shipment->documents` and associated `ImportDocument` records (`ImportDocument::where('shipment_id', $shipment->id)` or via batch).
- **Logic**:
  1. If `$shipment->nomor_bl_awb` is empty:
     $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`, Severity = `BLOCKING`.
  2. If associated `ImportDocument` records exist for this shipment:
     - Extract `bl_number` or `bill_of_lading` fields from each document.
     - Compare normalized strings (`strtoupper(trim($docBl))` vs `strtoupper(trim($shipment->nomor_bl_awb))`).
     - If any document B/L contradicts the primary B/L:
       $\rightarrow$ Status = **`Mismatch`**, `is_passed` = `false`.
  3. If all documents match or no contradicting B/L exists:
     $\rightarrow$ Status = **`Valid`**, `is_passed` = `true`.

#### Rule V02: Weight Ratio & Tolerance (`ruleV02WeightTolerance`)
- **Severity**: `WARNING`
- **Rule Code**: `V02`
- **Monitored Entity**: `Shipment $shipment`
- **Data Inputs**:
  - `$shipment->total_bruto` (Gross Weight)
  - `$shipment->total_netto` (Net Weight)
  - `$shipment->items->sum('berat_bruto')` (Items Accumulated Gross Weight)
  - `$shipment->items->sum('berat_netto')` (Items Accumulated Net Weight)
- **Logic**:
  1. If `$shipment->total_bruto <= 0` and `$shipment->total_netto <= 0`:
     $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`.
  2. If `$shipment->total_netto > $shipment->total_bruto` (physical impossibility: tare weight cannot be negative):
     $\rightarrow$ Status = **`Mismatch`**, `is_passed` = `false`.
  3. Compare Header Gross Weight with Line Items Accumulated Gross Weight:
     - Let $G_{\text{header}} = \text{total\_bruto}$, $G_{\text{items}} = \sum \text{berat\_bruto}$.
     - If $G_{\text{items}} > 0$:
       - Discrepancy $\Delta G = |G_{\text{header}} - G_{\text{items}}|$.
       - Variance ratio: $V = \frac{\Delta G}{G_{\text{header}}} \times 100\%$.
       - If $V > 0.5\%$:
         $\rightarrow$ Status = **`Warning`**, `is_passed` = `true` (Warning severity does not halt workflow).
  4. If $G_{\text{netto}} \le G_{\text{bruto}}$ and $V \le 0.5\%$:
     $\rightarrow$ Status = **`Valid`**, `is_passed` = `true`.

#### Rule V03: Invoice Header vs Line Items Sum (`ruleV03InvoiceItemsSum`)
- **Severity**: `WARNING`
- **Rule Code**: `V03`
- **Monitored Entity**: `Shipment $shipment`
- **Data Inputs**:
  - `$shipment->total_nilai_valuta` (Invoice Header Declared Total)
  - `$shipment->items->sum('nilai_fob')` (Line Items Accumulated FOB/CIF Total)
  - Individual `$item->nilai_fob` values.
- **Logic**:
  1. If `$shipment->total_nilai_valuta <= 0` or `$shipment->items->count() === 0`:
     $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`.
  2. If any item has a null or unparsed price while others have values:
     $\rightarrow$ Status = **`Requires Manual Review`**, `is_passed` = `false`.
  3. Calculate difference: $\Delta = |T_{\text{header}} - \sum T_{\text{items}}|$.
  4. If $\Delta > 1.00$ (exceeds $1.00 rounding tolerance):
     $\rightarrow$ Status = **`Mismatch`**, `is_passed` = `false`.
  5. If $\Delta \le 1.00$:
     $\rightarrow$ Status = **`Valid`**, `is_passed` = `true`.

#### Rule V04: ISO 6346 Container Checksum (`ruleV04ContainerChecksum`)
- **Severity**: `WARNING`
- **Rule Code**: `V04`
- **Monitored Entity**: `Shipment $shipment`
- **Data Inputs**:
  - `$shipment->containers` (relational collection of `ShipmentContainer`)
  - Fallback: `$shipment->nomor_kontainer` (denormalized string)
- **Logic**:
  1. Extract container numbers:
     - If `$shipment->containers()->count() > 0`: evaluate each `ShipmentContainer->container_number`.
     - Otherwise, parse container numbers from `$shipment->nomor_kontainer` using regex `/([A-Z]{4})[- ]?([0-9]{7})/`.
  2. If shipment has `$shipment->total_kontainer > 0` but no container numbers could be extracted:
     $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`.
  3. For each container number:
     - Check regex format `^[A-Z]{4}[0-9]{7}$`. If not compliant:
       $\rightarrow$ Status = **`Warning`**, `is_passed` = `true` (e.g. non-ISO format or LCL indicator).
     - Calculate ISO 6346 Modulo-11 Check Digit:
       - Letter weights: A=10, B=12, C=13, D=14, E=15, F=16, G=17, H=18, I=19, J=20, K=21, L=23, M=24, N=25, O=26, P=27, Q=28, R=29, S=30, T=31, U=32, V=34, W=35, X=36, Y=37, Z=38.
       - Position weights $2^i$ ($i = 0 \dots 9$).
       - Sum $S = \sum_{i=0}^9 \text{val}(c_i) \times 2^i$.
       - Remainder $R = S \pmod{11}$.
       - Expected check digit $D = R \pmod{10}$ (remainder 10 yields digit 0).
       - Compare $D$ with actual 11th digit.
       - If mismatch:
         $\rightarrow$ Status = **`Mismatch`**, `is_passed` = `false`.
  4. If all container numbers pass:
     $\rightarrow$ Status = **`Valid`**, `is_passed` = `true`.

#### Rule V05: 8-Digit BTKI HS Code Validity (`ruleV05HsCodeBtki`)
- **Severity**: `BLOCKING`
- **Rule Code**: `V05`
- **Monitored Entity**: `Shipment $shipment`
- **Data Inputs**:
  - `$shipment->items` (`ShipmentItem` collection)
  - `CeisaHsCodeService::getTariffAndLartas()` / `HsCode` database
- **Logic**:
  1. If `$shipment->items->count() === 0`:
     $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`.
  2. For each item:
     - Clean `kode_hs`: remove `.` (dots), `-` (dashes), and spaces.
     - If empty: $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`.
     - If `strlen($cleanHs) !== 8` or `!ctype_digit($cleanHs)`:
       $\rightarrow$ Status = **`Mismatch`**, `is_passed` = `false`.
     - If `hs_confidence_score` exists and is `< 0.80`:
       $\rightarrow$ Status = **`Requires Manual Review`**, `is_passed` = `false`.
     - Check existence in BTKI database (`HsCode::where('code', $item->kode_hs)...` or via `CeisaHsCodeService`):
       - If pos tarif is completely absent from BTKI:
         $\rightarrow$ Status = **`Mismatch`**, `is_passed` = `false`.
  3. If all items pass 8-digit verification and exist in BTKI:
     $\rightarrow$ Status = **`Valid`**, `is_passed` = `true`.

#### Rule V06: Currency & KMK Customs Exchange Rate (`ruleV06CurrencyAndKmk`)
- **Severity**: `BLOCKING`
- **Rule Code**: `V06`
- **Monitored Entity**: `Shipment $shipment`
- **Data Inputs**:
  - `$shipment->kode_valuta`
  - `$shipment->ndbm_kurs`
  - Reference date: `$shipment->tgl_bl_awb ?? now()`
  - `CeisaKursService::getActiveKurs()`
- **Logic**:
  1. If `$shipment->kode_valuta` is empty:
     $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`.
  2. Recognized foreign currency whitelist:
     `['USD', 'EUR', 'CNY', 'SGD', 'JPY', 'IDR', 'GBP', 'AUD', 'MYR']`.
     - If currency is not in whitelist:
       $\rightarrow$ Status = **`Mismatch`**, `is_passed` = `false`.
  3. Query active KMK exchange rate via `CeisaKursService::getActiveKurs($valuta, $date)`:
     - If rate is zero or query fails completely:
       $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`.
     - If rate exists and recorded `$shipment->ndbm_kurs > 0`:
       - If recorded kurs differs by > 5% from official KMK rate:
         $\rightarrow$ Status = **`Warning`**, `is_passed` = `true` (Advisory to refresh kurs).
  4. If currency is recognized and active KMK rate is confirmed:
     $\rightarrow$ Status = **`Valid`**, `is_passed` = `true`.

#### Rule V07: Importer/Exporter NPWP 16-Digit PMK 112/2022 (`ruleV07Npwp16Digit`)
- **Severity**: `BLOCKING`
- **Rule Code**: `V07`
- **Monitored Entity**: `Shipment $shipment`
- **Data Inputs**:
  - `$shipment->client?->npwp`
- **Logic**:
  1. Clean NPWP: `preg_replace('/[^0-9]/', '', $client->npwp ?? '')`.
  2. If empty:
     $\rightarrow$ Status = **`Missing Data`**, `is_passed` = `false`.
  3. Length validation:
     - If length $\ne 15$ and length $\ne 16$:
       $\rightarrow$ Status = **`Mismatch`**, `is_passed` = `false`.
     - If length $== 15$:
       $\rightarrow$ Status = **`Warning`**, `is_passed` = `true` (Valid legacy 15-digit NPWP, advisory note for PMK 112/2022 16-digit NIK/NPWP transition).
     - If length $== 16$:
       $\rightarrow$ Status = **`Valid`**, `is_passed` = `true` (Fully compliant with PMK 112/2022).

---

### 2.3 Blocking Decision Logic (`isSubmissionBlocked`)

A CEISA customs submission (BC 2.0 or BC 3.0) must be protected by a strict pre-flight guard:

$$\text{Blocked} \iff \exists r \in \text{Rules} \text{ such that } \text{Severity}(r) = \text{BLOCKING} \land (\neg \text{IsPassed}(r) \lor \text{Status}(r) \in \{\text{Mismatch}, \text{Missing Data}, \text{Requires Manual Review}\})$$

Rules with severity `WARNING` (V02, V03, V04) do **not** block CEISA submission even if they return `Warning` or `Mismatch`; they only raise alerts on the supervisor dashboard and require audit logging.

```php
public function isSubmissionBlocked(array $validationResults): bool
{
    if (isset($validationResults['is_blocked'])) {
        return (bool) $validationResults['is_blocked'];
    }

    $rules = $validationResults['rules'] ?? $validationResults;
    foreach ($rules as $rule) {
        $severity = strtoupper($rule['severity'] ?? 'WARNING');
        $status = $rule['status'] ?? '';
        $isPassed = $rule['is_passed'] ?? ($status === 'Valid');

        if ($severity === 'BLOCKING') {
            if (!$isPassed || in_array($status, ['Mismatch', 'Missing Data', 'Requires Manual Review'], true)) {
                return true;
            }
        }
    }

    return false;
}
```

---

## 3. Caveats

1. **OCR Confidence Score Storage**:
   - `ShipmentItem` already has `hs_confidence_score` (DECIMAL 2). When document extraction parses cargo items, it must populate this field so that Rule V05 can accurately trigger `Requires Manual Review` when confidence drops below 0.80.
2. **Container Normalization Phasing**:
   - In Milestone 1, container data transitions from a single string (`shipment.nomor_kontainer`) to relational rows (`shipment_containers`). Rule V04 must check `shipment->containers` first, and gracefully fall back to regex extraction from `nomor_kontainer` if relational rows are not yet seeded.
3. **CEISA Test Environment & Mock Mode**:
   - When running under test environments or mock mode (`config('ceisa.api.mock_mode') = true`), `CeisaKursService` uses cached or static fallback rates. Rule V06 must accept these fallback rates as valid active KMK rates so test suites execute deterministically without live DJBC network access.

---

## 4. Conclusion & Complete Design Blueprint

### 4.1 Interface Contract & Output Structure
`CrossDocumentValidationService::validateShipment(Shipment $shipment): array` returns:

```php
[
    'is_blocked' => bool,
    'summary' => [
        'valid' => int,
        'warning' => int,
        'mismatch' => int,
        'missing_data' => int,
        'manual_review' => int,
    ],
    'rules' => [
        [
            'rule_code' => 'V01',
            'rule_name' => 'Konsistensi Nomor B/L Antar-Dokumen',
            'severity' => 'BLOCKING',
            'status' => 'Valid', // 'Valid' | 'Warning' | 'Mismatch' | 'Missing Data' | 'Requires Manual Review'
            'is_passed' => true,
            'message' => 'Nomor B/L konsisten di seluruh dokumen: BL-WANHAI-8891.',
            'field_references' => ['nomor_bl_awb'],
            'details' => [
                'shipment_bl' => 'BL-WANHAI-8891',
                'matched_documents_count' => 3,
            ]
        ],
        [
            'rule_code' => 'V02',
            'rule_name' => 'Toleransi Bobot Kotor vs Bobot Bersih',
            'severity' => 'WARNING',
            'status' => 'Valid',
            'is_passed' => true,
            'message' => 'Rasio berat kargo wajar dan konsisten (Netto: 7800 kg <= Bruto: 8500 kg).',
            'field_references' => ['total_bruto', 'total_netto'],
            'details' => [
                'gross_weight' => 8500.0,
                'net_weight' => 7800.0,
                'items_gross_sum' => 8500.0,
                'variance_percent' => 0.0,
            ]
        ],
        [
            'rule_code' => 'V03',
            'rule_name' => 'Sinkronisasi Total Header vs Akumulasi Item',
            'severity' => 'WARNING',
            'status' => 'Valid',
            'is_passed' => true,
            'message' => 'Total nilai invoice cocok dengan rincian item barang (Selisih: $0.00 <= $1.00).',
            'field_references' => ['total_nilai_valuta'],
            'details' => [
                'header_total' => 25000.0,
                'items_total' => 25000.0,
                'difference' => 0.0,
                'currency' => 'USD',
            ]
        ],
        [
            'rule_code' => 'V04',
            'rule_name' => 'Validasi Checksum Kontainer ISO 6346',
            'severity' => 'WARNING',
            'status' => 'Valid',
            'is_passed' => true,
            'message' => 'Seluruh nomor kontainer memenuhi standar ISO 6346 modulo-11.',
            'field_references' => ['nomor_kontainer'],
            'details' => [
                'containers_evaluated' => ['CSQU3054383'],
                'invalid_containers' => [],
            ]
        ],
        [
            'rule_code' => 'V05',
            'rule_name' => 'Validasi Keabsahan 8-Digit HS Code BTKI',
            'severity' => 'BLOCKING',
            'status' => 'Valid',
            'is_passed' => true,
            'message' => 'Seluruh HS Code (2 item) memenuhi format 8 digit dan terdaftar di BTKI 2022.',
            'field_references' => ['kode_hs'],
            'details' => [
                'total_items' => 2,
                'valid_items' => 2,
                'invalid_codes' => [],
            ]
        ],
        [
            'rule_code' => 'V06',
            'rule_name' => 'Kelengkapan Valuta Pabean & Kurs Pajak',
            'severity' => 'BLOCKING',
            'status' => 'Valid',
            'is_passed' => true,
            'message' => 'Valuta USD terdaftar dan kurs KMK aktif terkonfirmasi.',
            'field_references' => ['kode_valuta', 'ndbm_kurs'],
            'details' => [
                'currency' => 'USD',
                'active_kurs' => 15850.0,
            ]
        ],
        [
            'rule_code' => 'V07',
            'rule_name' => 'Validasi NPWP 16 Digit Importir (PMK 112/2022)',
            'severity' => 'BLOCKING',
            'status' => 'Valid',
            'is_passed' => true,
            'message' => 'Identitas NPWP importir memenuhi regulasi PMK 112/2022 (16 digit).',
            'field_references' => ['client_npwp'],
            'details' => [
                'client_name' => 'PT INDO JAYA MAKMUR',
                'npwp' => '0123456789012345',
                'digit_count' => 16,
            ]
        ],
    ]
]
```

### 4.2 Proposed Code Refactoring: `CrossDocumentValidationService.php`

```php
<?php

namespace App\Services\Shipment;

use App\Models\ImportDocument;
use App\Models\ImportField;
use App\Models\ImportValidation;
use App\Models\Shipment;
use App\Models\ShipmentContainer;
use App\Models\HsCode;
use App\Services\Ceisa\CeisaHsCodeService;
use App\Services\Ceisa\CeisaKursService;

class CrossDocumentValidationService
{
    protected CeisaHsCodeService $hsCodeService;
    protected CeisaKursService $kursService;

    public function __construct(CeisaHsCodeService $hsCodeService, CeisaKursService $kursService)
    {
        $this->hsCodeService = $hsCodeService;
        $this->kursService = $kursService;
    }

    /**
     * Run all cross-document rules (V01 - V07) on a primary Shipment entity.
     */
    public function validateShipment(Shipment $shipment): array
    {
        $rules = [];

        // V01: Konsistensi Nomor BL antar-dokumen (BLOCKING)
        $rules[] = $this->ruleV01BlConsistencyShipment($shipment);

        // V02: Toleransi Bobot Kotor & Bersih vs Akumulasi Item (WARNING)
        $rules[] = $this->ruleV02WeightToleranceShipment($shipment);

        // V03: Sinkronisasi Total Header Invoice vs Akumulasi Item (WARNING)
        $rules[] = $this->ruleV03InvoiceItemsSumShipment($shipment);

        // V04: Checksum Digit Kontainer ISO 6346 Modulo-11 (WARNING)
        $rules[] = $this->ruleV04ContainerChecksumShipment($shipment);

        // V05: Validasi Keabsahan 8-Digit HS Code BTKI (BLOCKING)
        $rules[] = $this->ruleV05HsCodeBtkiShipment($shipment);

        // V06: Validasi Valuta Asing & Tanggal Kurs KMK Pabean (BLOCKING)
        $rules[] = $this->ruleV06CurrencyAndKmkShipment($shipment);

        // V07: Validasi Nomor Entitas Importir & NPWP 16 Digit (BLOCKING)
        $rules[] = $this->ruleV07Npwp16DigitShipment($shipment);

        // Compute Summary Counts
        $summary = [
            'valid' => 0,
            'warning' => 0,
            'mismatch' => 0,
            'missing_data' => 0,
            'manual_review' => 0,
        ];

        foreach ($rules as $r) {
            match ($r['status']) {
                'Valid' => $summary['valid']++,
                'Warning' => $summary['warning']++,
                'Mismatch' => $summary['mismatch']++,
                'Missing Data' => $summary['missing_data']++,
                'Requires Manual Review' => $summary['manual_review']++,
                default => null,
            };
        }

        $isBlocked = $this->isSubmissionBlocked(['rules' => $rules]);

        return [
            'is_blocked' => $isBlocked,
            'summary' => $summary,
            'rules' => $rules,
        ];
    }

    /**
     * Helper to determine whether customs submission is blocked by any BLOCKING rule.
     */
    public function isSubmissionBlocked(array $validationResults): bool
    {
        $rules = $validationResults['rules'] ?? $validationResults;
        foreach ($rules as $rule) {
            $severity = strtoupper($rule['severity'] ?? 'WARNING');
            $status = $rule['status'] ?? '';
            $isPassed = $rule['is_passed'] ?? ($status === 'Valid');

            if ($severity === 'BLOCKING') {
                if (!$isPassed || in_array($status, ['Mismatch', 'Missing Data', 'Requires Manual Review'], true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Helper to retrieve plain text list of blocking violation messages.
     */
    public function getBlockingReasons(array $validationResults): array
    {
        $reasons = [];
        $rules = $validationResults['rules'] ?? $validationResults;
        foreach ($rules as $rule) {
            $severity = strtoupper($rule['severity'] ?? 'WARNING');
            $status = $rule['status'] ?? '';
            $isPassed = $rule['is_passed'] ?? ($status === 'Valid');

            if ($severity === 'BLOCKING' && (!$isPassed || in_array($status, ['Mismatch', 'Missing Data', 'Requires Manual Review'], true))) {
                $reasons[] = "[{$rule['rule_code']}] {$rule['rule_name']}: {$rule['message']}";
            }
        }

        return $reasons;
    }
```

### 4.3 Pipeline & Controller Integration Points

1. **`app/Services/Shipment/ShipmentWorkflowService.php`**:
   - Inject `CrossDocumentValidationService $validationService` into constructor.
   - Guard `submitCeisaAndAdvanceToDo(Shipment $shipment)`:
     ```php
     public function submitCeisaAndAdvanceToDo(Shipment $shipment): CeisaDocument
     {
         // 0. Pre-Flight Cross-Document Validation Gate
         $validation = $this->validationService->validateShipment($shipment);
         if ($this->validationService->isSubmissionBlocked($validation)) {
             $reasons = $this->validationService->getBlockingReasons($validation);
             throw new \DomainException("Pengiriman CEISA diblokir oleh Cross-Document Validation Engine:\n" . implode("\n", $reasons));
         }

         $ceisaDoc = $shipment->ceisaDocument;
         ...
     ```

2. **`app/Http/Controllers/BC20Controller.php` & `BC30Controller.php`**:
   - In `submit(CeisaDocument $bc)`:
     ```php
     $shipment = $bc->shipment ?? $this->workflowService->ensureShipmentForCeisaDocument($bc);
     $valResults = app(CrossDocumentValidationService::class)->validateShipment($shipment);
     if (app(CrossDocumentValidationService::class)->isSubmissionBlocked($valResults)) {
         $reasons = app(CrossDocumentValidationService::class)->getBlockingReasons($valResults);
         return back()->with('error', 'Pengiriman ke CEISA diblokir oleh Cross-Document Validation Engine: ' . implode('; ', $reasons));
     }
     ```
   - In `show(CeisaDocument $bc)`:
     Pass `$crossValidation = app(CrossDocumentValidationService::class)->validateShipment($shipment);` to the Blade view to render 5-status validation badges.

3. **`app/Http/Controllers/Api/CeisaApiController.php`**:
   - In `submitBC20` and `submitBC30`:
     Run `validateShipment($shipment)`. If blocked, return:
     ```json
     {
         "status": "error",
         "message": "Submission blocked by Cross-Document Validation Engine.",
         "blocking_reasons": ["..."],
         "summary": { ... }
     }
     ```
     with HTTP status `422 Unprocessable Entity`.

4. **Integration with State Machines (`ImportWorkflowStateMachine` & `ExportWorkflowStateMachine`)**:
   - Stage `IMP_05_CROSS_VALIDATED`: Executed automatically after OCR extraction.
   - Stage `IMP_08_CEISA_SUBMITTED`: Guarded by `!$validationService->isSubmissionBlocked($results)`. If blocked, transition is rejected.

---

## 5. Verification Method

To independently verify the implementation, the implementer must execute:

### 5.1 Test Suite Verification
Run the new unit test suite targeting all 7 rules, 5 statuses, and blocking behaviors:
```bash
php artisan test tests/Unit/CrossDocumentValidationShipmentTest.php
```

### 5.2 Verification Checklist
| Test Case | Condition | Expected Result |
|---|---|---|
| 1. V01 B/L Consistency | B/L matches across shipment and documents | Status `Valid`, `is_passed` = `true` |
| 2. V01 B/L Mismatch | Differing B/L numbers in attached documents | Status `Mismatch`, `is_passed` = `false`, blocks submission |
| 3. V01 B/L Missing | B/L is null/empty | Status `Missing Data`, `is_passed` = `false`, blocks submission |
| 4. V02 Weight Inversion | Net weight (12,000 kg) > Gross weight (10,000 kg) | Status `Mismatch`, `is_passed` = `false`, severity `WARNING` |
| 5. V02 Weight Tolerance | Gross weight matches items sum within 0.5% | Status `Valid`, `is_passed` = `true` |
| 6. V02 Weight Discrepancy | Gross weight deviates from items by > 0.5% | Status `Warning`, `is_passed` = `true` |
| 7. V03 Invoice Sum | Header $50,000 vs items $49,999.50 (diff $0.50) | Status `Valid`, `is_passed` = `true` |
| 8. V03 Invoice Mismatch | Header $50,000 vs items $48,000 (diff $2,000) | Status `Mismatch`, `is_passed` = `false`, severity `WARNING` |
| 9. V04 ISO 6346 Valid | Valid container `CSQU3054383` | Status `Valid`, `is_passed` = `true` |
| 10. V04 Remainder 10 | Container `TGHU1000050` with remainder 10 | Status `Valid`, check digit 0 recognized |
| 11. V04 Checksum Mismatch | Container `CSQU3054384` with invalid check digit | Status `Mismatch`, `is_passed` = `false` |
| 12. V05 HS 8-Digit Valid | 8-digit pos tarif `85044090` in BTKI | Status `Valid`, `is_passed` = `true` |
| 13. V05 HS Invalid Length | Non-8 digit HS `8504.40` (6 digits) | Status `Mismatch`, `is_passed` = `false`, blocks submission |
| 14. V05 HS Low Confidence | Confidence score 0.65 (< 0.80) | Status `Requires Manual Review`, `is_passed` = `false`, blocks submission |
| 15. V06 Currency Valid | Valid currency `USD` with active KMK rate | Status `Valid`, `is_passed` = `true` |
| 16. V06 Currency Invalid | Unknown currency `XYZ` | Status `Mismatch`, `is_passed` = `false`, blocks submission |
| 17. V07 NPWP 16-Digit | Valid 16-digit NPWP `0123456789012345` | Status `Valid`, `is_passed` = `true` |
| 18. V07 NPWP 15-Digit | Valid legacy 15-digit NPWP `012345678901234` | Status `Warning`, `is_passed` = `true` |
| 19. V07 NPWP Invalid | 12-digit NPWP `012345678901` | Status `Mismatch`, `is_passed` = `false`, blocks submission |
| 20. Helper `isSubmissionBlocked` | Any blocking rule failing (V01, V05, V06, V07) | Returns `true` |
| 21. Helper `isSubmissionBlocked` | Only warning rules failing (V02, V03, V04) | Returns `false` |

### 5.3 Invalidation Conditions
This investigation report shall be invalidated if:
1. `Shipment` schema removes or alters `nomor_bl_awb`, `total_bruto`, `total_netto`, `kode_valuta`, `total_nilai_valuta`, or `client_id`.
2. DJBC regulations deprecate PMK 112/2022 NPWP 16-digit or replace BTKI 2022 8-digit tariff classifications.
3. The project architecture decides to abandon the 5-status categorization engine in favor of a binary boolean pass/fail.
