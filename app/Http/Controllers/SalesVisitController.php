<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SalesVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SalesVisitController extends Controller
{
    /**
     * Daftar Riwayat Kunjungan Sales
     */
    public function index(Request $request)
    {
        Gate::authorize('crm.view');

        $user = auth()->user();
        $query = SalesVisit::with(['salesUser', 'client']);

        // Jika bukan pimpinan/eksekutif, hanya tampilkan kunjungan sales milik sendiri
        if (!in_array($user->role ?? '', ['R-OWN', 'R-MGR', 'R-SYS'])) {
            $query->where('sales_user_id', $user->id);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('result_status')) {
            $query->where('result_status', $request->result_status);
        }

        $visits = $query->latest('check_in_time')->paginate(15);
        $clients = Client::orderBy('name')->get();

        $stats = [
            'total' => SalesVisit::count(),
            'today' => SalesVisit::whereDate('check_in_time', today())->count(),
            'out_of_radius' => SalesVisit::where('is_out_of_radius', true)->count(),
            'deal_closed' => SalesVisit::where('result_status', 'DEAL_CLOSE')->count(),
        ];

        return view('crm.sales_visits_index', compact('visits', 'clients', 'stats'));
    }

    /**
     * Check-in Kunjungan Sales Lapangan dengan Geofencing 200m
     */
    public function checkIn(Request $request)
    {
        Gate::authorize('crm.deal');

        $validated = $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'nullable|string', // Base64 data URI atau path
            'out_of_radius_reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        // Koordinat acuan kantor klien (default jika belum diset: gunakan koordinat saat ini sebagai baseline)
        $clientLat = $client->latitude ?? $validated['latitude'];
        $clientLng = $client->longitude ?? $validated['longitude'];

        // Hitung jarak Geofencing Haversine (dalam meter)
        $distanceMeters = $this->calculateDistanceMeters(
            $validated['latitude'],
            $validated['longitude'],
            $clientLat,
            $clientLng
        );

        $isOutOfRadius = $distanceMeters > 200.0; // Melebihi radius toleransi 200 meter

        if ($isOutOfRadius && empty($validated['out_of_radius_reason'])) {
            return back()->with('error', "Anda berada {$distanceMeters} meter di luar radius kantor klien (>200m). Wajib menyertakan alasan tertulis check-in di luar radius.");
        }

        // Simpan foto bukti kunjungan
        $photoPath = null;
        if (!empty($validated['photo']) && str_starts_with($validated['photo'], 'data:image')) {
            try {
                $image = preg_replace('/^data:image\/\w+;base64,/', '', $validated['photo']);
                $image = str_replace(' ', '+', $image);
                $filename = 'visit_' . auth()->id() . '_' . time() . '.jpg';
                $destPath = 'visits/' . $filename;
                Storage::disk('public')->put($destPath, base64_decode($image));
                $photoPath = 'uploads/' . $destPath;
            } catch (\Exception $e) {
                $photoPath = null;
            }
        }

        $visit = SalesVisit::create([
            'sales_user_id' => auth()->id(),
            'client_id' => $client->id,
            'check_in_time' => now(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'distance_meters' => $distanceMeters,
            'is_out_of_radius' => $isOutOfRadius,
            'out_of_radius_reason' => $isOutOfRadius ? $validated['out_of_radius_reason'] : null,
            'photo_path' => $photoPath,
            'result_status' => 'FOLLOW_UP_REQUIRED',
            'notes' => $validated['notes'] ?? null,
        ]);

        $msg = "Check-in kunjungan di {$client->name} berhasil dicatat.";
        if ($isOutOfRadius) {
            $msg .= " Peringatan: Tercatat di luar radius toleransi ({$distanceMeters} m).";
        }

        return redirect()->route('crm.sales-visits.index')->with('success', $msg);
    }

    /**
     * Check-out dan Rekap Hasil Kunjungan
     */
    public function checkOut(Request $request, string $id)
    {
        Gate::authorize('crm.deal');

        $validated = $request->validate([
            'result_status' => 'required|in:INTERESTED,DEAL_CLOSE,FOLLOW_UP_REQUIRED,REJECTED,NEEDS_QUOTATION',
            'notes' => 'required|string',
        ]);

        $visit = SalesVisit::findOrFail($id);
        $visit->update([
            'check_out_time' => now(),
            'result_status' => $validated['result_status'],
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', "Check-out kunjungan selesai. Hasil: {$validated['result_status']}.");
    }

    /**
     * Rumus Haversine untuk menghitung jarak dua titik koordinat GPS dalam Meter.
     */
    protected function calculateDistanceMeters($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
