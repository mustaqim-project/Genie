<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan middleware proteksi RBAC level Controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:setting.view', only: ['index']),
            new Middleware('permission:setting.manage', except: ['index']),
        ];
    }

    public function index()
    {
        $users = User::with('roles')->latest()->paginate(10);
        $roles = Role::with('permissions', 'users')->get();
        $permissions = Permission::all()->groupBy('module');
        $config = config('ceisa');
        $logs = CeisaApiLog::latest()->take(10)->get();

        return view('setting.index', compact('users', 'roles', 'permissions', 'config', 'logs'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
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

        return back()->with('success', "Pengguna {$user->name} berhasil ditambahkan dengan peran terkait.");
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'job_title' => $validated['job_title'],
            'department' => $validated['department'],
        ]);

        $user->roles()->sync([$validated['role_id']]);

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->roles()->sync([$request->role_id]);

        return back()->with('success', "Peran pengguna {$user->name} berhasil diperbarui.");
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif.');
        }

        if ($user->email === 'admin@agungtrans.co.id') {
            return back()->with('error', 'Akun Administrator Utama tidak boleh dinonaktifkan.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status akun {$user->name} berhasil {$statusStr}.");
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', "Kata sandi untuk pengguna {$user->name} berhasil diatur ulang.");
    }

    public function destroyUser(User $user)
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

        return back()->with('success', "Pengguna {$name} berhasil dihapus dari sistem.");
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $permissionIds = $request->input('permissions', []);
        $role->permissions()->sync($permissionIds);
        $count = count($permissionIds);

        return back()->with('success', "Matriks hak akses untuk peran [{$role->code}] {$role->name} berhasil diperbarui ({$count} izin aktif).");
    }
}
