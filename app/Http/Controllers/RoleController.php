<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Tampilkan Cockpit Manajemen Peran & Hak Akses (RBAC) bergaya Apple HIG / Cooca Core.
     */
    public function index(): View
    {
        $roles = Role::with(['permissions', 'users'])->get();
        $members = User::with('roles')->latest()->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('settings.roles', compact('roles', 'members', 'permissions'));
    }

    /**
     * Buat Role Custom Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50', 'unique:roles,code'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $code = !empty($validated['code']) 
            ? strtoupper(trim($validated['code']))
            : 'R-' . strtoupper(Str::slug($validated['name']));

        if (Role::where('code', $code)->exists()) {
            return back()->with('error', "Kode role '{$code}' sudah digunakan. Silakan gunakan kode lain.");
        }

        $role = Role::create([
            'name' => trim($validated['name']),
            'code' => $code,
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $permissionIds = Permission::whereIn('id', $validated['permissions'])
                ->orWhereIn('code', $validated['permissions'])
                ->pluck('id');
            $role->permissions()->sync($permissionIds);
        }

        return back()->with('success', "Role custom [{$role->code}] '{$role->name}' berhasil dibuat.");
    }

    /**
     * Perbarui Role & Matriks Permissions
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        // Lindungi perubahan nama role sistem fundamental jika perlu
        $role->update([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $permissionIds = [];
        if (!empty($validated['permissions'])) {
            $permissionIds = Permission::whereIn('id', $validated['permissions'])
                ->orWhereIn('code', $validated['permissions'])
                ->pluck('id');
        }

        $role->permissions()->sync($permissionIds);

        return back()->with('success', "Hak akses dan konfigurasi peran [{$role->code}] '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Hapus Role Custom
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->code, ['R-ADM', 'R-OWN'])) {
            return back()->with('error', "Role sistem [{$role->code}] bersifat terproteksi dan tidak boleh dihapus.");
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "Role [{$role->code}] masih digunakan oleh {$role->users()->count()} anggota tim. Pindahkan peran mereka terlebih dahulu sebelum menghapus.");
        }

        $name = $role->name;
        $code = $role->code;
        $role->permissions()->detach();
        $role->delete();

        return back()->with('success', "Role [{$code}] '{$name}' berhasil dihapus.");
    }

    /**
     * Tambah Anggota Tim Baru dari Cockpit RBAC
     */
    public function storeMember(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'job_title' => $validated['job_title'],
            'department' => $validated['department'],
            'is_active' => true,
        ]);

        $user->roles()->sync([$validated['role_id']]);

        return back()->with('success', "Anggota tim '{$user->name}' berhasil didaftarkan ke sistem.");
    }

    /**
     * Ubah Peran Anggota Tim Secara Cepat
     */
    public function updateMemberRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->roles()->sync([$validated['role_id']]);
        $role = Role::find($validated['role_id']);

        return back()->with('success', "Peran anggota '{$user->name}' berhasil diubah menjadi [{$role->code}] {$role->name}.");
    }

    /**
     * Hapus / Nonaktifkan Anggota Tim
     */
    public function destroyMember(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->email === 'admin@agungtrans.co.id') {
            return back()->with('error', 'Akun Administrator Sistem utama tidak dapat dihapus.');
        }

        $name = $user->name;
        $user->roles()->detach();
        $user->delete();

        return back()->with('success', "Anggota tim '{$name}' berhasil dihapus dari sistem.");
    }
}
