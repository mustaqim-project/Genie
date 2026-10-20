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

        $waSetting = \App\Models\WhatsappSetting::first() ?? \App\Models\WhatsappSetting::create([
            'waba_id' => '104928374829103',
            'phone_number_id' => '109283746501928',
            'display_phone_number' => '+62 812-9000-8888',
            'verified_name' => 'PT AGUNG TRANS LOGISTICS',
            'quality_rating' => 'GREEN',
            'messaging_limit_tier' => 'TIER_10K',
            'is_connected' => true,
            'auto_send_sppb' => true,
            'auto_send_npe' => true,
            'auto_send_do' => true,
            'auto_send_billing' => true,
        ]);

        $socialAccounts = \App\Models\SocialMediaAccount::all();
        $kantorList = config('ceisa.kantor_pabean', []);
        $settings = \App\Models\SystemSetting::getAllGrouped();

        return view('setting.index', compact('users', 'roles', 'permissions', 'config', 'logs', 'waSetting', 'socialAccounts', 'kantorList', 'settings'));
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

    /**
     * Update WhatsApp Meta WABA Settings from Centralized Settings Hub.
     */
    public function updateWaSettings(Request $request)
    {
        $validated = $request->validate([
            'display_phone_number' => 'required|string|max:50',
            'verified_name' => 'required|string|max:150',
            'phone_number_id' => 'nullable|string|max:100',
            'waba_id' => 'nullable|string|max:100',
            'access_token' => 'nullable|string',
            'webhook_verify_token' => 'nullable|string|max:100',
        ]);

        $setting = \App\Models\WhatsappSetting::first() ?? new \App\Models\WhatsappSetting();
        $setting->display_phone_number = $validated['display_phone_number'];
        $setting->verified_name = $validated['verified_name'];
        if (!empty($validated['phone_number_id'])) $setting->phone_number_id = $validated['phone_number_id'];
        if (!empty($validated['waba_id'])) $setting->waba_id = $validated['waba_id'];
        if (!empty($validated['access_token'])) $setting->access_token = $validated['access_token'];
        if (!empty($validated['webhook_verify_token'])) $setting->webhook_verify_token = $validated['webhook_verify_token'];

        $setting->auto_send_sppb = $request->has('auto_send_sppb');
        $setting->auto_send_npe = $request->has('auto_send_npe');
        $setting->auto_send_do = $request->has('auto_send_do');
        $setting->auto_send_billing = $request->has('auto_send_billing');
        $setting->is_connected = true;
        $setting->save();

        return redirect()->route('setting.index', ['tab' => 'waba'])
            ->with('success', 'Konfigurasi WhatsApp Meta WABA resmi perusahaan berhasil disimpan.');
    }

    /**
     * Hubungkan Akun Media Sosial Resmi Baru dari Menu Setting.
     */
    public function connectSocialAccount(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:linkedin,instagram,facebook,tiktok,youtube',
            'account_name' => 'required|string|max:150',
            'username' => 'required|string|max:100',
        ]);

        \App\Models\SocialMediaAccount::updateOrCreate(
            ['platform' => $validated['platform']],
            [
                'account_id' => 'acc_' . \Illuminate\Support\Str::random(12),
                'account_name' => $validated['account_name'],
                'username' => ltrim($validated['username'], '@'),
                'status' => 'active',
                'followers_count' => rand(800, 4500),
                'metadata' => ['connected_at' => now()->toIso8601String()],
            ]
        );

        return redirect()->route('setting.index', ['tab' => 'social'])
            ->with('success', "Akun " . strtoupper($validated['platform']) . " resmi berhasil dihubungkan ke sistem.");
    }

    /**
     * Putuskan Akun Media Sosial dari Menu Setting.
     */
    public function disconnectSocialAccount($id)
    {
        $account = \App\Models\SocialMediaAccount::findOrFail($id);
        $account->update(['status' => 'disconnected']);

        return redirect()->route('setting.index', ['tab' => 'social'])
            ->with('success', "Koneksi akun {$account->account_name} ({$account->platform}) telah dinonaktifkan.");
    }

    /**
     * Simpan Pengaturan Gateway CEISA 4.0 dan Profil Entitas DJBC ke Basis Data (Tanpa .env)
     */
    public function updateCeisaSettings(Request $request)
    {
        $validated = $request->validate([
            // Environment & Gateway
            'ceisa_mode' => 'required|in:sandbox,production',
            'ceisa_base_url' => 'required|url',
            'ceisa_auth_url' => 'required|url',
            'ceisa_refresh_token_url' => 'nullable|url',
            'ceisa_username' => 'required|string|max:100',
            'ceisa_password' => 'nullable|string|max:100',
            'ceisa_api_key' => 'required|string|max:100',
            'ceisa_application_id' => 'nullable|string|max:100',
            'ceisa_whatsapp_no' => 'nullable|string|max:50',

            // Profil Perusahaan
            'company_name' => 'required|string|max:200',
            'company_nib' => 'required|string|max:50',
            'company_npwp' => 'required|string|max:50',
            'company_address' => 'required|string|max:500',
            'company_kode_kantor' => 'required|string|max:20',
            'company_id_perusahaan' => 'required|string|max:20',
        ]);

        $mockMode = $validated['ceisa_mode'] === 'sandbox' ? '1' : '0';

        \App\Models\SystemSetting::set('ceisa_env', $validated['ceisa_mode'], 'ceisa', 'Lingkungan CEISA 4.0');
        \App\Models\SystemSetting::set('ceisa_mock_mode', $mockMode, 'ceisa', 'Mode Mock Simulator');
        \App\Models\SystemSetting::set('ceisa_base_url', $validated['ceisa_base_url'], 'ceisa', 'Base URL Gateway DJBC');
        \App\Models\SystemSetting::set('ceisa_auth_url', $validated['ceisa_auth_url'], 'ceisa', 'Auth Login URL DJBC');
        if (!empty($validated['ceisa_refresh_token_url'])) {
            \App\Models\SystemSetting::set('ceisa_refresh_token_url', $validated['ceisa_refresh_token_url'], 'ceisa', 'Refresh Token URL');
        }
        \App\Models\SystemSetting::set('ceisa_username', $validated['ceisa_username'], 'ceisa', 'Username API DJBC');
        if (!empty($validated['ceisa_password'])) {
            \App\Models\SystemSetting::set('ceisa_password', $validated['ceisa_password'], 'ceisa', 'Password API DJBC');
        }
        \App\Models\SystemSetting::set('ceisa_api_key', $validated['ceisa_api_key'], 'ceisa', 'API Key Resmi DJBC');
        \App\Models\SystemSetting::set('ceisa_application_id', $validated['ceisa_application_id'] ?? '', 'ceisa', 'Application ID Resmi DJBC');
        \App\Models\SystemSetting::set('ceisa_whatsapp_no', $validated['ceisa_whatsapp_no'] ?? '', 'ceisa', 'Nomor WhatsApp Notifikasi DJBC');

        \App\Models\SystemSetting::set('company_name', $validated['company_name'], 'company', 'Nama Resmi Perusahaan');
        \App\Models\SystemSetting::set('company_nib', $validated['company_nib'], 'company', 'Nomor Induk Berusaha');
        \App\Models\SystemSetting::set('company_npwp', $validated['company_npwp'], 'company', 'NPWP 15/16 Digit');
        \App\Models\SystemSetting::set('company_address', $validated['company_address'], 'company', 'Alamat Resmi Sesuai NIB');
        \App\Models\SystemSetting::set('company_kode_kantor', $validated['company_kode_kantor'], 'company', 'Kode Kantor Pabean Default');
        \App\Models\SystemSetting::set('company_id_perusahaan', $validated['company_id_perusahaan'], 'company', 'ID Unik Perusahaan untuk Nomor Aju');

        // Reload configuration into runtime immediately
        \App\Models\SystemSetting::loadIntoConfig();

        // Clear cached token so new credentials take effect immediately
        app(\App\Services\Ceisa\CeisaAuthService::class)->clearToken();

        $modeLabel = $validated['ceisa_mode'] === 'production' ? 'LIVE PRODUCTION (Gateway DJBC Asli)' : 'SANDBOX / SIMULASI';

        return redirect()->route('setting.index', ['tab' => 'gateway'])
            ->with('success', "Pengaturan Gateway CEISA 4.0 dan Profil Entitas berhasil disimpan ke basis data! Mode aktif: {$modeLabel}.");
    }
}
