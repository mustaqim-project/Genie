<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentTemplateController extends Controller
{
    /**
     * Tampilkan Daftar Seluruh Template Dokumen & Email (CRUD Index)
     */
    public function index(Request $request)
    {
        $category = $request->query('category', 'all');
        $division = $request->query('division', 'all');
        $search = $request->query('search');

        $query = DocumentTemplate::query();

        if ($category !== 'all' && !empty($category)) {
            $query->where('category', $category);
        }

        if ($division !== 'all' && !empty($division)) {
            $query->where('division', $division);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhere('terms_and_conditions', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderByDesc('is_default')
                           ->orderBy('category')
                           ->orderBy('name')
                           ->paginate(15)
                           ->withQueryString();

        $categories = DocumentTemplate::CATEGORIES;
        $divisions = DocumentTemplate::DIVISIONS;

        // KPI Counts
        $kpis = [
            'total' => DocumentTemplate::count(),
            'email' => DocumentTemplate::where('category', 'EMAIL')->count(),
            'surat_jalan' => DocumentTemplate::where('category', 'SURAT_JALAN')->count(),
            'klausul' => DocumentTemplate::where('category', 'TERMS_KLAUSUL')->count(),
        ];

        return view('settings.templates_index', compact('templates', 'categories', 'divisions', 'kpis', 'category', 'division', 'search'));
    }

    /**
     * Simpan Template Baru (Create)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'nullable|string|max:100|unique:document_templates,code',
            'category' => 'required|string|in:EMAIL,SURAT_JALAN,TERMS_KLAUSUL,WHATSAPP',
            'division' => 'nullable|string|in:marketing,finance,document,traffic,general',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'header_notes' => 'nullable|string',
            'footer_notes' => 'nullable|string',
            'signee_name' => 'nullable|string|max:150',
            'emergency_phone' => 'nullable|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        if (empty($validated['code'])) {
            $baseCode = Str::upper(Str::slug($validated['category'] . '_' . ($validated['division'] ?? 'GEN') . '_' . $validated['name'], '_'));
            $validated['code'] = substr($baseCode, 0, 80) . '_' . Str::upper(Str::random(4));
        }

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = true;
        $validated['is_system'] = false;

        if ($validated['is_default']) {
            DocumentTemplate::where('category', $validated['category'])
                ->when($validated['division'], fn($q) => $q->where('division', $validated['division']))
                ->update(['is_default' => false]);
        }

        $template = DocumentTemplate::create($validated);

        return redirect()->route('settings.templates.index', ['category' => $template->category])
                         ->with('success', "Template '{$template->name}' berhasil ditambahkan ke sistem.");
    }

    /**
     * Update Template yang Sudah Ada (Update)
     */
    public function update(Request $request, string $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:100|unique:document_templates,code,' . $template->id,
            'category' => 'required|string|in:EMAIL,SURAT_JALAN,TERMS_KLAUSUL,WHATSAPP',
            'division' => 'nullable|string|in:marketing,finance,document,traffic,general',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'header_notes' => 'nullable|string',
            'footer_notes' => 'nullable|string',
            'signee_name' => 'nullable|string|max:150',
            'emergency_phone' => 'nullable|string|max:100',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $template->is_active;

        if ($validated['is_default'] && !$template->is_default) {
            DocumentTemplate::where('category', $validated['category'])
                ->when($validated['division'], fn($q) => $q->where('division', $validated['division']))
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }

        $template->update($validated);

        return redirect()->back()->with('success', "Template '{$template->name}' berhasil diperbarui.");
    }

    /**
     * Hapus Template (Delete)
     */
    public function destroy(string $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        if ($template->is_system) {
            return redirect()->back()->with('error', "Template sistem bawaan ({$template->name}) dilindungi dan tidak dapat dihapus. Anda dapat mengedit isinya.");
        }

        $name = $template->name;
        $category = $template->category;
        $template->delete();

        return redirect()->route('settings.templates.index', ['category' => $category])
                         ->with('success', "Template '{$name}' berhasil dihapus.");
    }

    /**
     * Duplikasi Template (Clone)
     */
    public function duplicate(string $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $clone = $template->replicate();
        $clone->name = $template->name . ' (Salinan)';
        $clone->code = Str::upper(substr($template->code, 0, 70) . '_COPY_' . Str::random(4));
        $clone->is_default = false;
        $clone->is_system = false;
        $clone->save();

        return redirect()->route('settings.templates.index', ['category' => $clone->category])
                         ->with('success', "Template berhasil diduplikasi sebagai '{$clone->name}'.");
    }

    /**
     * Atur sebagai Template Default
     */
    public function setDefault(string $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        DocumentTemplate::where('category', $template->category)
            ->when($template->division, fn($q) => $q->where('division', $template->division))
            ->update(['is_default' => false]);

        $template->update(['is_default' => true, 'is_active' => true]);

        return redirect()->back()->with('success', "Template '{$template->name}' kini menjadi format default.");
    }

    /**
     * Live Preview Template dengan Data Sampel (JSON / Modal)
     */
    public function preview(string $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $sampleData = [
            'client_name' => 'PT INDO SARI MAKMUR',
            'contact_person' => 'Ibu Ratna Dewi',
            'quotation_no' => 'QUO-2026-0042',
            'invoice_no' => 'INV-2026-0118',
            'nomor_aju' => '000000-004291-20261117-001290',
            'project_no' => 'PRJ-2026-JKT-098',
            'bl_number' => 'MSK9817263541',
            'vessel_name' => 'MV EVER GIVEN',
            'voyage_no' => '042N',
            'pol' => 'SHANGHAI (CNSHA)',
            'origin_port' => 'Shanghai (CNSHA)',
            'pod' => 'TANJUNG PRIOK (IDTPP)',
            'destination_port' => 'Tanjung Priok (IDTPP)',
            'eta_date' => now()->addDays(3)->format('d F Y, 14:00 WIB'),
            'total_amount' => 'Rp 38.500.000',
            'valid_until' => now()->addDays(30)->format('d F Y'),
            'due_date' => now()->addDays(14)->format('d F Y'),
            'document_type' => 'BC 2.0 (PIB Impor)',
            'shipment_status' => 'Kapal Sandar di Dermaga JICT 1',
            'tps_location' => 'JICT 1, Tanjung Priok',
            'container_no' => 'TGHU9182736',
            'seal_no' => 'BC-SEAL-8821',
            'truck_plate' => 'B 9281 KHA',
            'driver_name' => 'Slamet Supriyadi',
            'custom_notes' => 'Catatan Tambahan: Harap sertakan stempel basah pada lembar serah terima barang.',
            'bank_info' => "Bank Mandiri Cabang Tanjung Perak: 141-00-9876543-2\nBank BCA KCP Surabaya: 010-8899-771\nAtas Nama: PT GENIE LOGISTIK INDONESIA",
        ];

        $rendered = $template->render($sampleData);

        return response()->json([
            'template' => $template,
            'rendered' => $rendered,
            'sample_data' => $sampleData,
        ]);
    }
}
