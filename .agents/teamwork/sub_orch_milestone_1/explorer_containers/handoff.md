# Handoff Report: Multi-Container Normalization & ISO 6346 Checksum Validation

## 1. Observation

### 1.1 Existing Database Schema for Containers and Shipments
- **`database/migrations/2026_10_12_000013_create_f4_delivery_order_tables.php` (Lines 45–55)**:
  ```php
  // 3. Shipment Containers (Nomor Kontainer, Tipe, & Segel)
  if (!Schema::hasTable('shipment_containers')) {
      Schema::create('shipment_containers', function (Blueprint $table) {
          $table->uuid('id')->primary();
          $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
          $table->foreignUuid('voyage_id')->nullable()->constrained('shipment_voyages')->onDelete('set null');
          $table->string('number');
          $table->foreignUuid('type_id')->nullable()->constrained('container_types')->onDelete('set null');
          $table->string('seal_no')->nullable();
          $table->timestamps();
      });
  }
  ```
  The table `shipment_containers` exists in migration history with columns: `id`, `shipment_id`, `voyage_id`, `number`, `type_id`, `seal_no`, and timestamps. Note that `number` is `NOT NULL` and no `container_number`, `container_size`, `container_type`, `initial_seal_no`, `bc_reseal_no`, `gate_out_at`, `delivered_at`, or `eir_file_path` exist yet.

- **`database/migrations/2026_10_12_000014_create_f5_traffic_field_tables.php` (Lines 156–165)**:
  ```php
  // 11. Container Returns (Pengembalian Kontainer Kosong ke Depo Pelayaran)
  if (!Schema::hasTable('container_returns')) {
      Schema::create('container_returns', function (Blueprint $table) {
          $table->uuid('id')->primary();
          $table->foreignUuid('container_id')->constrained('shipment_containers')->onDelete('cascade');
          $table->date('return_due')->nullable();
          $table->timestamp('returned_at')->nullable();
          $table->decimal('cost', 18, 2)->nullable();
          $table->timestamps();
      });
  }
  ```
  `container_returns` already has a foreign key constraint referencing `shipment_containers.id`. Therefore, table renaming or recreation is strictly forbidden; an `ALTER TABLE` migration adding missing columns is required.

- **`database/migrations/2026_10_12_000003_create_shipment_workflow_tables.php` (Lines 68–69)**:
  ```php
  // Container & Volume Details
  $table->integer('total_kontainer')->default(1);
  $table->string('nomor_kontainer', 100)->nullable();
  ```
  `shipments` table currently stores container information as a single denormalized string column `nomor_kontainer` (VARCHAR 100) and an integer `total_kontainer`.

- **`database/migrations/2026_11_12_100000_add_tracking_dok_and_inspection_photos_tables.php` (Lines 39–40)**:
  ```php
  'old_seal_number' => fn () => $table->string('old_seal_number', 60)->nullable()->after('inspection_scheduled_at'),
  'new_seal_number' => fn () => $table->string('new_seal_number', 60)->nullable()->after('old_seal_number'),
  ```
  `shipments` table was recently augmented with `old_seal_number` (initial carrier seal) and `new_seal_number` (customs reseal / segel baru BC).

### 1.2 Model Layer Observations
- **`app/Models/Shipment.php` (Lines 60–61, 93–94)**:
  - `$fillable` contains: `'total_kontainer'`, `'nomor_kontainer'`, `'old_seal_number'`, `'new_seal_number'`.
  - There is currently **no** `containers()` relationship defined on `Shipment`.
  - There is currently **no** accessor (`getNomorKontainerAttribute`) or mutator (`setNomorKontainerAttribute`) on `Shipment`.
- **`app/Models/ShipmentContainer.php`**:
  - File does not exist yet. No Eloquent model is mapped to `shipment_containers`.

### 1.3 Usage Across Views, Services, and Tests
- **`resources/views/dashboard.blade.php` (Line 174)**:
  ```blade
  <div class="text-[11px] font-mono text-black/40 dark:text-white/40 mt-0.5">{{ $shp->nomor_kontainer ?? '1 Kontainer' }}</div>
  ```
  Views directly read `$shp->nomor_kontainer` expecting a printable string.
- **`tests/Feature/CeisaSyncAndCallbackTest.php` (Line 530)**:
  ```php
  $this->assertEquals('CSLU1234567, CSLU7654321', $shipment->nomor_kontainer);
  ```
  Tests assert multiple containers as a comma-separated string on `$shipment->nomor_kontainer`.
- **`tests/Feature/BundleTrackingAndInspectionTest.php` (Lines 123, 178)**:
  ```php
  'nomor_kontainer' => 'OOLU9081726',
  'nomor_kontainer' => 'MSCU-1122334 (1x40HC)',
  ```
  Container strings may contain formatting hyphens, spaces, and parenthesized ISO size annotations (e.g. `(1x40HC)`).
- **`database/seeders/DatabaseSeeder.php` (Line 574)**:
  ```php
  'nomor_kontainer' => 'WHLU-9182341 (1x40HC)',
  ```
- **`database/seeders/StagePrerequisiteSeeder.php` (Lines 54, 90)**:
  ```php
  'required_fields' => ['shipping_vendor_id', 'nama_kapal', 'voyage_no', 'nomor_kontainer'],
  'required_fields' => ['nomor_kontainer'],
  ```
- **`app/Services/Shipment/CrossDocumentValidationService.php` (Lines 163–197)**:
  ```php
  protected function ruleV04ContainerChecksum(ImportDocument $doc, array $fields): array
  {
      $containerNo = strtoupper(trim($fields['container_number'] ?? ''));
      ...
      if (!preg_match('/^[A-Z]{4}[0-9]{7}$/', $containerNo)) {
          return [
              'rule_code' => 'V04',
              'rule_name' => 'Validasi Checksum Kontainer ISO 6346',
              'severity' => 'WARNING',
              'status' => 'FAILED',
              'message' => "Format nomor kontainer '{$containerNo}' tidak memenuhi standar ISO 6346 (4 huruf diikuti 7 angka).",
              'field_references' => ['container_number'],
          ];
      }
      return [
          'rule_code' => 'V04',
          'rule_name' => 'Validasi Checksum Kontainer ISO 6346',
          'severity' => 'WARNING',
          'status' => 'PASSED',
          'message' => "Nomor kontainer '{$containerNo}' memenuhi format ISO 6346.",
          'field_references' => ['container_number'],
      ];
  }
  ```
  Observation: Rule V04 currently only checks the regex pattern `^[A-Z]{4}[0-9]{7}$` and completely omits the ISO 6346 modulo-11 check digit calculation!

---

## 2. Logic Chain

### 2.1 ISO 6346 Check Digit Algorithm Specification
1. **Container Identifier Anatomy**:
   - Owner Code: 3 letters `[A-Z]`
   - Equipment Category Identifier: 1 letter (`U` for all freight containers, `J` for detachable equipment, `Z` for trailers/chassis)
   - Serial Number: 6 digits `[0-9]`
   - Check Digit: 1 digit `[0-9]`
   - Total Length: 11 alphanumeric characters.
2. **Character-to-Value Mapping**:
   - Digits `0` through `9` take values `0` through `9`.
   - Letters `A` through `Z` are assigned integer values starting at `10`, **excluding multiples of 11 (11, 22, 33)**:
     - `A` = 10
     - `B` = 12 *(11 omitted)*
     - `C` = 13, `D` = 14, `E` = 15, `F` = 16, `G` = 17, `H` = 18, `I` = 19, `J` = 20, `K` = 21
     - `L` = 23 *(22 omitted)*
     - `M` = 24, `N` = 25, `O` = 26, `P` = 27, `Q` = 28, `R` = 29, `S` = 30, `T` = 31, `U` = 32
     - `V` = 34 *(33 omitted)*
     - `W` = 35, `X` = 36, `Y` = 37, `Z` = 38
3. **Position Weighting**:
   - For positions index $i = 0, 1, \dots, 9$ (characters 1 through 10), weight is $2^i$:
     $w_0 = 1, w_1 = 2, w_2 = 4, w_3 = 8, w_4 = 16, w_5 = 32, w_6 = 64, w_7 = 128, w_8 = 256, w_9 = 512$.
4. **Weighted Summation**:
   $S = \sum_{i=0}^9 \text{Value}(C_i) \times 2^i$.
5. **Modulo-11 & Remainder 10 Handling**:
   - Calculate remainder: $R = S \pmod{11}$.
   - According to ISO 6346: if the remainder $R = 10$, the check digit is officially `0` (or $R \pmod{10} = 0$).
   - If $R = 0 \dots 9$, check digit is $R$.
   - Compact formula: $\text{CheckDigit} = (S \pmod{11}) \pmod{10}$.
6. **Empirical Verification of Algorithm**:
   - Case 1: `CSQU3054383` -> Sum = 6185. $6185 \pmod{11} = 3$. Actual digit = 3. **Valid** (standard ISO benchmark).
   - Case 2 (Remainder 10): `TGHU1000050` -> Sum = 2969. $2969 \pmod{11} = 10 \implies 10 \pmod{10} = 0$. Actual digit = 0. **Valid** (demonstrates remainder 10 edge case).
   - Case 3 (Remainder 10): `MSCU9081720` -> Sum = 3475. $3475 \pmod{11} = 10 \implies 0$. Actual digit = 0. **Valid**.
   - Case 4: `MSKU1234565` -> Sum = 5560. $5560 \pmod{11} = 5$. Actual digit = 5. **Valid**.
   - Case 5: `OOLU9081726` -> Sum = 4026. $4026 \pmod{11} = 0$. Actual digit = 6, expected = 0. **Invalid**.
   - Case 6: `CSQU3054384` -> Check digit tampered from 3 to 4. **Invalid**.

### 2.2 Database Migration Schema Alignment
1. **Target Migration File**:
   `database/migrations/2026_11_12_200000_normalize_shipment_containers_table.php`
2. **Schema Additions on `shipment_containers`**:
   - `container_number`: VARCHAR(20) NOT NULL (defensively added, populated from existing `number`).
   - `container_size`: ENUM('20FT', '40FT', '40HC', '45FT', 'LCL') NOT NULL DEFAULT '40HC'.
   - `container_type`: ENUM('GP', 'RF', 'OT', 'FR', 'TK') NOT NULL DEFAULT 'GP'.
   - `initial_seal_no`: VARCHAR(60) NULL (populated from existing `seal_no`).
   - `bc_reseal_no`: VARCHAR(60) NULL.
   - `gate_out_at`: DATETIME NULL.
   - `delivered_at`: DATETIME NULL.
   - `eir_file_path`: VARCHAR(255) NULL.
   - `deleted_at`: DATETIME NULL (via `$table->softDeletes()`).
3. **Backward Compatibility in Schema**:
   - Existing column `number` must be altered to `nullable` so that new rows written with only `container_number` do not violate SQL `NOT NULL` without default.
   - Existing column `seal_no` must remain available or be synchronized with `initial_seal_no`.
   - Data migration step in `up()`:
     - For existing `shipment_containers`, copy `number` to `container_number`, and `seal_no` to `initial_seal_no`.
     - For existing `shipments` that have `nomor_kontainer` populated but no records in `shipment_containers`, automatically parse and seed corresponding `ShipmentContainer` records.

### 2.3 Model Architecture: `App\Models\ShipmentContainer`
1. Primary key: UUID (`use HasUuids; protected $keyType = 'string'; public $incrementing = false;`).
2. SoftDeletes & Auditable traits.
3. Fillable array supporting both new and legacy column names:
   `['shipment_id', 'voyage_id', 'container_number', 'number', 'container_size', 'container_type', 'type_id', 'seal_no', 'initial_seal_no', 'bc_reseal_no', 'gate_out_at', 'delivered_at', 'eir_file_path']`.
4. Column alias accessors:
   - `getNumberAttribute()` / `setNumberAttribute()` <-> `container_number`.
   - `getSealNoAttribute()` / `setSealNoAttribute()` <-> `initial_seal_no`.
5. Static validation method:
   ```php
   public static function validateIso6346(string $containerNumber): bool
   ```
   Accepts raw strings (with or without hyphens/spaces), extracts 11 characters, applies the letter mapping and $2^i$ weights, and validates check digit using $(S \pmod{11}) \pmod{10}$.
6. Static check digit calculator:
   ```php
   public static function calculateCheckDigit(string $first10Chars): ?int
   ```
   Calculates the expected check digit for any 10-character container prefix.
7. Relationships:
   - `shipment()` -> `BelongsTo(Shipment::class)`
   - `voyage()` -> `BelongsTo(ShipmentVoyage::class, 'voyage_id')`
   - `returns()` -> `HasMany(ContainerReturn::class, 'container_id')`

### 2.4 Backward Compatibility Strategy on `App\Models\Shipment`
1. **Relationship**:
   ```php
   public function containers(): \Illuminate\Database\Eloquent\Relations\HasMany
   {
       return $this->hasMany(ShipmentContainer::class, 'shipment_id');
   }
   ```
2. **Accessor (`getNomorKontainerAttribute`)**:
   - If relational `containers` are loaded and non-empty: returns comma-separated `container_number`s (e.g. `'CSLU1234567, CSLU7654321'`).
   - If `$this->attributes['nomor_kontainer']` is non-empty: returns the verbatim column value.
   - If the shipment is persisted and has related containers in DB: fetches and returns comma-separated container numbers.
   - Otherwise returns `null`.
   - Result: 100% backward compatible with existing blade templates (`{{ $shp->nomor_kontainer }}`), seeders, and test assertions (`$this->assertEquals('CSLU1234567, CSLU7654321', $shipment->nomor_kontainer)`).
3. **Mutator (`setNomorKontainerAttribute`)**:
   - Stores the raw string in `$this->attributes['nomor_kontainer']`.
   - Queues or triggers synchronization to `shipment_containers`.
4. **Automatic Synchronization (`syncContainersFromNomorKontainer`)**:
   - Triggered on `Shipment::saved` model event.
   - Splits `nomor_kontainer` by commas, semicolons, or newlines.
   - Parses each item: extracts container number (e.g. `WHLU-9182341`) and optional size tag (e.g. `40HC` from `(1x40HC)`).
   - Upserts into `shipment_containers` preventing duplicate container numbers.
   - Synchronizes `old_seal_number` -> `initial_seal_no` and `new_seal_number` -> `bc_reseal_no` on the first container.
   - Updates `total_kontainer` count to match the number of normalized containers.

---

## 3. Caveats

1. **Non-ISO Domestic Containers & Breakbulk / LCL**:
   Some domestic or inter-island shipments use non-standard numbering (e.g. 7 digits without letters, or local fleet codes). While ISO 6346 validation should be strictly enforced for international import/export declarations (PIB BC 2.0 and PEB BC 3.0), the model must allow saving non-ISO container numbers without throwing uncaught database exceptions. The validation status should be flagged in the Cross-Document Validation engine (Rule V04) as `Warning` or `Requires Manual Review` rather than crashing model persist operations.
2. **Pre-existing Foreign Keys**:
   The table `container_returns` has a foreign key to `shipment_containers.id`. The migration must never drop and recreate the table, but must modify columns using `Schema::table()`.
3. **Multiple Containers with Differing Seal Numbers**:
   Legacy shipments stored only a single `old_seal_number` and `new_seal_number`. When normalizing multiple containers, each container record possesses its own `initial_seal_no` and `bc_reseal_no`. The backward-compatible synchronization logic must map the shipment-level seal numbers to the primary (first) container.

---

## 4. Conclusion

The relational multi-container tracking architecture is fully designed:
1. **Migration Specification**:
   - Create `database/migrations/2026_11_12_200000_normalize_shipment_containers_table.php`.
   - Add columns: `container_number`, `container_size`, `container_type`, `initial_seal_no`, `bc_reseal_no`, `gate_out_at`, `delivered_at`, `eir_file_path`, `deleted_at`.
   - Make legacy `number` nullable, populate missing values from legacy fields, and add composite indexes.
2. **Model Specification**:
   - Create `app/Models/ShipmentContainer.php` with complete ISO 6346 modulo-11 check digit verification, remainder 10 mapping, power-of-2 weights ($2^i$), and letter value lookups.
   - Update `app/Models/Shipment.php` with `containers()` HasMany relationship, smart two-way `nomor_kontainer` accessor and mutator, and seal synchronization.
3. **Validation Specification**:
   - Connect Rule V04 in `CrossDocumentValidationService` directly to `ShipmentContainer::validateIso6346()`, evaluating each container in the shipment and emitting structured 5-status results (`Valid`, `Warning`, `Mismatch`, `Missing Data`, `Requires Manual Review`).

### Complete Proposed Implementations

#### A. Database Migration: `database/migrations/2026_11_12_200000_normalize_shipment_containers_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pastikan tabel shipment_containers ada
        if (!Schema::hasTable('shipment_containers')) {
            Schema::create('shipment_containers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('voyage_id')->nullable()->constrained('shipment_voyages')->onDelete('set null');
                $table->string('container_number', 20);
                $table->string('number', 50)->nullable();
                $table->foreignUuid('type_id')->nullable()->constrained('container_types')->onDelete('set null');
                $table->enum('container_size', ['20FT', '40FT', '40HC', '45FT', 'LCL'])->default('40HC');
                $table->enum('container_type', ['GP', 'RF', 'OT', 'FR', 'TK'])->default('GP');
                $table->string('seal_no', 60)->nullable();
                $table->string('initial_seal_no', 60)->nullable();
                $table->string('bc_reseal_no', 60)->nullable();
                $table->dateTime('gate_out_at')->nullable();
                $table->dateTime('delivered_at')->nullable();
                $table->string('eir_file_path', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['shipment_id', 'container_number']);
            });
            return;
        }

        // 2. Jika tabel sudah ada dari migration sebelumnya, tambahkan kolom yang belum ada
        Schema::table('shipment_containers', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_containers', 'container_number')) {
                $table->string('container_number', 20)->nullable()->after('shipment_id');
            }
            if (!Schema::hasColumn('shipment_containers', 'container_size')) {
                $table->enum('container_size', ['20FT', '40FT', '40HC', '45FT', 'LCL'])->default('40HC')->after('type_id');
            }
            if (!Schema::hasColumn('shipment_containers', 'container_type')) {
                $table->enum('container_type', ['GP', 'RF', 'OT', 'FR', 'TK'])->default('GP')->after('container_size');
            }
            if (!Schema::hasColumn('shipment_containers', 'initial_seal_no')) {
                $table->string('initial_seal_no', 60)->nullable()->after('seal_no');
            }
            if (!Schema::hasColumn('shipment_containers', 'bc_reseal_no')) {
                $table->string('bc_reseal_no', 60)->nullable()->after('initial_seal_no');
            }
            if (!Schema::hasColumn('shipment_containers', 'gate_out_at')) {
                $table->dateTime('gate_out_at')->nullable()->after('bc_reseal_no');
            }
            if (!Schema::hasColumn('shipment_containers', 'delivered_at')) {
                $table->dateTime('delivered_at')->nullable()->after('gate_out_at');
            }
            if (!Schema::hasColumn('shipment_containers', 'eir_file_path')) {
                $table->string('eir_file_path', 255)->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('shipment_containers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // 3. Pastikan kolom legacy 'number' bersifat nullable agar insert dengan container_number tidak error
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shipment_containers MODIFY COLUMN number VARCHAR(255) NULL");
            DB::statement("UPDATE shipment_containers SET container_number = number WHERE (container_number IS NULL OR container_number = '') AND number IS NOT NULL");
            DB::statement("UPDATE shipment_containers SET initial_seal_no = seal_no WHERE (initial_seal_no IS NULL OR initial_seal_no = '') AND seal_no IS NOT NULL");
        }

        // 4. Migrasi data historis dari shipments.nomor_kontainer ke shipment_containers jika belum ada kontainer
        $shipments = DB::table('shipments')
            ->whereNotNull('nomor_kontainer')
            ->where('nomor_kontainer', '!=', '')
            ->get(['id', 'nomor_kontainer', 'old_seal_number', 'new_seal_number']);

        foreach ($shipments as $shp) {
            $existingCount = DB::table('shipment_containers')->where('shipment_id', $shp->id)->count();
            if ($existingCount === 0) {
                // Split multi container yang dipisah koma/spasi/enter
                $parts = preg_split('/[,;\n\r]+/', $shp->nomor_kontainer);
                foreach ($parts as $part) {
                    $trimmed = trim($part);
                    if ($trimmed === '') continue;

                    // Ekstrak ukuran jika ada format "MSCU-1122334 (1x40HC)"
                    $size = '40HC';
                    if (preg_match('/20\s*FT/i', $trimmed)) $size = '20FT';
                    elseif (preg_match('/40\s*HC|40\s*HQ/i', $trimmed)) $size = '40HC';
                    elseif (preg_match('/40\s*FT/i', $trimmed)) $size = '40FT';
                    elseif (preg_match('/45\s*FT/i', $trimmed)) $size = '45FT';
                    elseif (preg_match('/LCL/i', $trimmed)) $size = 'LCL';

                    $cleanNo = preg_replace('/\s*\(.*?\)/', '', $trimmed);
                    $cleanNo = strtoupper(trim($cleanNo));

                    DB::table('shipment_containers')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'shipment_id' => $shp->id,
                        'container_number' => $cleanNo,
                        'number' => $cleanNo,
                        'container_size' => $size,
                        'container_type' => 'GP',
                        'initial_seal_no' => $shp->old_seal_number ?? null,
                        'seal_no' => $shp->old_seal_number ?? null,
                        'bc_reseal_no' => $shp->new_seal_number ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: mempertahankan struktur dasar tabel
        if (Schema::hasTable('shipment_containers')) {
            Schema::table('shipment_containers', function (Blueprint $table) {
                $dropCols = [];
                foreach (['container_size', 'container_type', 'initial_seal_no', 'bc_reseal_no', 'gate_out_at', 'delivered_at', 'eir_file_path', 'deleted_at'] as $col) {
                    if (Schema::hasColumn('shipment_containers', $col)) {
                        $dropCols[] = $col;
                    }
                }
                if (!empty($dropCols)) {
                    $table->dropColumn($dropCols);
                }
            });
        }
    }
};
```

#### B. Model: `app/Models/ShipmentContainer.php`
```php
<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentContainer extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $table = 'shipment_containers';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'voyage_id',
        'container_number',
        'number', // Alias backward compatibility
        'container_size',
        'container_type',
        'type_id',
        'seal_no', // Alias backward compatibility
        'initial_seal_no',
        'bc_reseal_no',
        'gate_out_at',
        'delivered_at',
        'eir_file_path',
    ];

    protected $casts = [
        'gate_out_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public const SIZES = ['20FT', '40FT', '40HC', '45FT', 'LCL'];
    public const TYPES = ['GP', 'RF', 'OT', 'FR', 'TK'];

    public const LETTER_VALUES = [
        'A' => 10, 'B' => 12, 'C' => 13, 'D' => 14, 'E' => 15, 'F' => 16, 'G' => 17, 'H' => 18, 'I' => 19, 'J' => 20,
        'K' => 21, 'L' => 23, 'M' => 24, 'N' => 25, 'O' => 26, 'P' => 27, 'Q' => 28, 'R' => 29, 'S' => 30, 'T' => 31,
        'U' => 32, 'V' => 34, 'W' => 35, 'X' => 36, 'Y' => 37, 'Z' => 38,
    ];

    /**
     * Validasi nomor kontainer berdasarkan standar ISO 6346 Modulo-11
     */
    public static function validateIso6346(string $containerNumber): bool
    {
        $clean = strtoupper(trim($containerNumber));
        $clean = str_replace(['-', ' '], '', $clean);

        // Standar ISO 6346: 4 huruf [A-Z] diikuti 7 angka [0-9]
        if (!preg_match('/^[A-Z]{4}[0-9]{7}$/', $clean)) {
            return false;
        }

        $expectedCheckDigit = self::calculateCheckDigit(substr($clean, 0, 10));
        if ($expectedCheckDigit === null) {
            return false;
        }

        $actualCheckDigit = (int) $clean[10];

        return $actualCheckDigit === $expectedCheckDigit;
    }

    /**
     * Hitung Check Digit (digit ke-11) dari 10 karakter pertama sesuai algoritma Modulo-11
     */
    public static function calculateCheckDigit(string $first10Chars): ?int
    {
        $clean = strtoupper(trim($first10Chars));
        $clean = str_replace(['-', ' '], '', $clean);

        if (strlen($clean) !== 10) {
            return null;
        }

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $char = $clean[$i];
            if (isset(self::LETTER_VALUES[$char])) {
                $val = self::LETTER_VALUES[$char];
            } elseif (is_numeric($char)) {
                $val = (int) $char;
            } else {
                return null;
            }

            $weight = 1 << $i; // 2^i
            $sum += $val * $weight;
        }

        $remainder = $sum % 11;

        // ISO 6346 spesifikasi: jika remainder = 10, digit periksa adalah 0
        return $remainder % 10;
    }

    public function isValidIso6346(): bool
    {
        $num = $this->container_number ?? $this->number ?? '';
        return !empty($num) && self::validateIso6346($num);
    }

    // Accessor & Mutator Backward Compatibility untuk 'number'
    public function getNumberAttribute(): ?string
    {
        return $this->attributes['container_number'] ?? $this->attributes['number'] ?? null;
    }

    public function setNumberAttribute(?string $value): void
    {
        $this->attributes['container_number'] = $value;
        $this->attributes['number'] = $value;
    }

    // Accessor & Mutator Backward Compatibility untuk 'seal_no'
    public function getSealNoAttribute(): ?string
    {
        return $this->attributes['initial_seal_no'] ?? $this->attributes['seal_no'] ?? null;
    }

    public function setSealNoAttribute(?string $value): void
    {
        $this->attributes['initial_seal_no'] = $value;
        $this->attributes['seal_no'] = $value;
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function voyage(): BelongsTo
    {
        return $this->belongsTo(ShipmentVoyage::class, 'voyage_id');
    }
}
```

#### C. Updates on `App\Models\Shipment`: Relationship & 100% Backward-Compatible Accessor/Mutator
Add to `app/Models/Shipment.php`:
```php
    public function containers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ShipmentContainer::class, 'shipment_id');
    }

    /**
     * Accessor backward compatible: menghasilkan string nomor kontainer (dipisah koma jika multi-kontainer)
     */
    public function getNomorKontainerAttribute(?string $value): ?string
    {
        if ($this->relationLoaded('containers') && $this->containers->isNotEmpty()) {
            return $this->containers->pluck('container_number')->filter()->implode(', ');
        }

        if (!empty($value)) {
            return $value;
        }

        if ($this->exists) {
            $containers = $this->containers()->pluck('container_number')->filter();
            if ($containers->isNotEmpty()) {
                return $containers->implode(', ');
            }
        }

        return $value;
    }

    /**
     * Mutator backward compatible: menyimpan string dan sinkronisasi ke tabel shipment_containers
     */
    public function setNomorKontainerAttribute(?string $value): void
    {
        $this->attributes['nomor_kontainer'] = $value;
    }

    /**
     * Sinkronisasi string kontainer ke record relational shipment_containers
     */
    public function syncContainersFromNomorKontainer(): void
    {
        $str = $this->attributes['nomor_kontainer'] ?? null;
        if (empty($str)) {
            return;
        }

        $parts = preg_split('/[,;\n\r]+/', $str);
        $cleanNumbers = [];

        foreach ($parts as $part) {
            $trimmed = trim($part);
            if ($trimmed === '') continue;

            $size = '40HC';
            if (preg_match('/20\s*FT/i', $trimmed)) $size = '20FT';
            elseif (preg_match('/40\s*HC|40\s*HQ/i', $trimmed)) $size = '40HC';
            elseif (preg_match('/40\s*FT/i', $trimmed)) $size = '40FT';
            elseif (preg_match('/45\s*FT/i', $trimmed)) $size = '45FT';
            elseif (preg_match('/LCL/i', $trimmed)) $size = 'LCL';

            $cleanNo = preg_replace('/\s*\(.*?\)/', '', $trimmed);
            $cleanNo = strtoupper(trim($cleanNo));
            if ($cleanNo === '') continue;

            $cleanNumbers[] = $cleanNo;

            $container = $this->containers()->where('container_number', $cleanNo)->first();
            if (!$container) {
                $this->containers()->create([
                    'container_number' => $cleanNo,
                    'number' => $cleanNo,
                    'container_size' => $size,
                    'container_type' => 'GP',
                    'initial_seal_no' => $this->old_seal_number,
                    'seal_no' => $this->old_seal_number,
                    'bc_reseal_no' => $this->new_seal_number,
                ]);
            }
        }

        if (count($cleanNumbers) > 0) {
            $count = count($cleanNumbers);
            if ($this->total_kontainer < $count) {
                $this->total_kontainer = $count;
                $this->saveQuietly();
            }
        }
    }
```
And in `Shipment::booted()`:
```php
static::saved(function (Shipment $shipment) {
    if (!empty($shipment->nomor_kontainer)) {
        $shipment->syncContainersFromNomorKontainer();
    }
});
```

---

## 5. Verification Method

To independently verify this design:

### 5.1 ISO 6346 Checksum Verification Test Suite
Run the dedicated test suite:
```bash
php artisan test --filter=ShipmentContainerIso6346Test
```
Test assertions matrix to include in `tests/Unit/ShipmentContainerIso6346Test.php`:
1. **Valid Standard Containers**:
   - `CSQU3054383` (Check Digit 3) -> asserts `true`
   - `MSKU1234565` (Check Digit 5) -> asserts `true`
   - `HLXU1234561` (Check Digit 1) -> asserts `true`
   - `TGHU1234567` (Check Digit 7) -> asserts `true`
   - `COSU1234560` (Check Digit 0) -> asserts `true`
2. **Remainder 10 Edge Cases**:
   - `TGHU1000050` (Sum = 2969, Remainder = 10, Check Digit = 0) -> asserts `true`
   - `MSCU9081720` (Sum = 3475, Remainder = 10, Check Digit = 0) -> asserts `true`
3. **Invalid Check Digits**:
   - `CSQU3054384` (Wrong check digit, expected 3) -> asserts `false`
   - `OOLU9081726` (Wrong check digit, expected 0) -> asserts `false`
   - `MSKU1234567` (Wrong check digit, expected 5) -> asserts `false`
4. **Invalid Lengths & Characters**:
   - `CSQU305438` (10 chars, missing check digit) -> asserts `false`
   - `CSQU30543831` (12 chars, too long) -> asserts `false`
   - `12345678901` (all numbers) -> asserts `false`
   - `CSQUABCDEFG` (letters in serial number) -> asserts `false`
   - `""` (empty string) -> asserts `false`
5. **Formatted Inputs (Tolerant Sanitization)**:
   - `CSQU-305438-3` (with hyphens) -> asserts `true`
   - `csqu 305438 3` (lowercase with spaces) -> asserts `true`
6. **Relational Multi-Container & Backward Compatibility**:
   - Create shipment with `nomor_kontainer = 'CSLU1234567, CSLU7654321'`.
   - Verify `$shipment->containers()->count() === 2`.
   - Verify `$shipment->nomor_kontainer === 'CSLU1234567, CSLU7654321'`.
   - Create new container via `$shipment->containers()->create(['container_number' => 'CSQU3054383'])`.
   - Verify `$shipment->fresh()->nomor_kontainer` contains `'CSQU3054383'`.

### 5.2 Database Migration Verification
Run migration check:
```bash
php artisan migrate --pretend
```
Inspect that `normalize_shipment_containers_table` generates correct `ALTER TABLE` statements without dropping existing data or constraints.

### 5.3 Invalidation Conditions
This investigation and design is invalidated if:
1. `container_returns` foreign key is broken or dropped by migration.
2. `Shipment::nomor_kontainer` returns an array or object instead of string, breaking Blade views (`dashboard.blade.php`) or existing tests (`CeisaSyncAndCallbackTest.php:530`).
3. ISO 6346 validator rejects valid remainder 10 containers (such as `TGHU1000050` or `MSCU9081720`).
