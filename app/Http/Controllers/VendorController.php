<?php

namespace App\Http\Controllers;

use App\Models\ShippingVendor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class VendorController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan middleware proteksi RBAC level Controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:do.view|setting.view|crm.view', only: ['index', 'show']),
            new Middleware('permission:do.manage|setting.manage|crm.manage', only: ['store', 'update', 'destroy']),
        ];
    }
    /**
     * Tampilkan daftar Master Vendor (Pelayaran / Shipping Lines & Logistik).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = ShippingVendor::withCount('shipments')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        $vendors = $query->paginate(15);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'data' => $vendors,
            ]);
        }

        return view('vendors.index', compact('vendors', 'search'));
    }

    /**
     * Simpan Master Vendor baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:30|unique:shipping_vendors,code',
            'pic_name' => 'nullable|string|max:100',
            'pic_email' => 'nullable|email|max:100',
            'pic_phone' => 'nullable|string|max:50',
            'portal_url' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
        ]);

        if (empty($validated['code'])) {
            $count = ShippingVendor::count() + 1;
            $validated['code'] = 'VND-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        }

        $vendor = ShippingVendor::create(array_merge($validated, ['is_active' => true]));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Vendor {$vendor->name} berhasil ditambahkan.",
                'data' => $vendor,
            ]);
        }

        return back()->with('success', "Vendor {$vendor->name} ({$vendor->code}) berhasil didaftarkan ke sistem.");
    }

    /**
     * Tampilkan detail vendor.
     */
    public function show(string $id)
    {
        $vendor = ShippingVendor::with(['shipments' => function ($q) {
            $q->latest()->take(10);
        }])->withCount('shipments')->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'data' => $vendor,
            ]);
        }

        return view('vendors.show', compact('vendor'));
    }

    /**
     * Perbarui data vendor.
     */
    public function update(Request $request, string $id)
    {
        $vendor = ShippingVendor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pic_name' => 'nullable|string|max:100',
            'pic_email' => 'nullable|email|max:100',
            'pic_phone' => 'nullable|string|max:50',
            'portal_url' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $vendor->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Data vendor {$vendor->name} berhasil diperbarui.",
                'data' => $vendor,
            ]);
        }

        return back()->with('success', "Data vendor {$vendor->name} ({$vendor->code}) berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / nonaktif vendor.
     */
    public function toggleStatus(Request $request, string $id)
    {
        $vendor = ShippingVendor::findOrFail($id);
        $vendor->is_active = !$vendor->is_active;
        $vendor->save();

        $statusStr = $vendor->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Vendor {$vendor->name} berhasil {$statusStr}.",
                'is_active' => $vendor->is_active,
            ]);
        }

        return back()->with('success', "Vendor {$vendor->name} berhasil {$statusStr}.");
    }

    /**
     * Hapus vendor (soft delete).
     */
    public function destroy(string $id)
    {
        $vendor = ShippingVendor::findOrFail($id);
        $vendor->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Vendor {$vendor->name} berhasil dihapus dari sistem.",
            ]);
        }

        return back()->with('success', "Vendor {$vendor->name} berhasil dihapus.");
    }
}
