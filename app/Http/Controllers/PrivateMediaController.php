<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class PrivateMediaController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan middleware proteksi RBAC level Controller
     */
    public static function middleware(): array
    {
        return [
            // showAttendancePhoto dapat dibuka via signed URL atau user terotentikasi
        ];
    }
    /**
     * Sajikan foto absensi terenkripsi / privat hanya untuk yang berhak atau via signed URL.
     * Sesuai ketentuan kepatuhan UU PDP No. 27/2022.
     */
    public function showAttendancePhoto(Request $request, string $attendanceLogId): Response
    {
        $log = AttendanceLog::findOrFail($attendanceLogId);

        // Otorisasi akses
        $hasValidSignature = $request->hasValidSignature();
        $user = auth()->user();
        $isAuthorizedUser = auth()->check() && (
            $user->can('hrm.manage') ||
            $user->can('setting.manage') ||
            $user->hasRole(['R-HR', 'R-ADM', 'R-OWN']) ||
            $user->employee?->id === $log->employee_id ||
            $user->id === $log->employee?->user_id
        );

        if (!$hasValidSignature && !auth()->check()) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }
            return redirect()->guest(route('login'));
        }

        if (!$hasValidSignature && !$isAuthorizedUser) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang atau tautan telah kedaluwarsa sesuai UU PDP No. 27/2022.');
        }

        if (empty($log->photo_url)) {
            abort(404, 'Foto presensi tidak tersedia.');
        }

        // Jika foto_url berupa data URI base64
        if (str_starts_with($log->photo_url, 'data:image')) {
            $data = preg_replace('/^data:image\/\w+;base64,/', '', $log->photo_url);
            $binary = base64_decode(str_replace(' ', '+', $data));
            return response($binary, 200, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }

        // Ambil nama berkas
        $filename = basename($log->photo_url);
        $privatePath = storage_path('app/private/attendances/' . $filename);
        $publicPath = public_path('uploads/attendances/' . $filename);

        $targetPath = null;
        if (\Illuminate\Support\Facades\Storage::disk('private')->exists('attendances/' . $filename)) {
            $targetPath = \Illuminate\Support\Facades\Storage::disk('private')->path('attendances/' . $filename);
        } elseif (File::exists($privatePath)) {
            $targetPath = $privatePath;
        } elseif (File::exists($publicPath)) {
            $targetPath = $publicPath;
        }

        if (!$targetPath) {
            abort(404, 'Berkas fisik foto presensi tidak ditemukan di storage.');
        }

        $mimeType = File::mimeType($targetPath) ?: 'image/jpeg';

        $response = response()->file($targetPath, [
            'Content-Type' => $mimeType,
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
        $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');

        return $response;
    }

    /**
     * Sajikan foto/data biometrik wajah pegawai secara privat.
     */
    public function showBiometric(Request $request, string $employeeId): Response
    {
        $employee = Employee::findOrFail($employeeId);

        $hasValidSignature = $request->hasValidSignature();
        $isAuthorizedUser = auth()->check() && (
            auth()->user()->can('hrm.manage') ||
            auth()->user()->can('setting.manage') ||
            auth()->user()->employee?->id === $employee->id
        );

        if (!$hasValidSignature && !auth()->check()) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }
            return redirect()->guest(route('login'));
        }

        if (!$hasValidSignature && !$isAuthorizedUser) {
            abort(403, 'Akses ditolak: Memerlukan otorisasi HR atau Pimpinan sesuai ketentuan UU PDP.');
        }

        if (empty($employee->photo_url)) {
            abort(404, 'Foto profil pegawai tidak tersedia.');
        }

        $filename = basename($employee->photo_url);
        $privatePath = storage_path('app/private/biometrics/' . $filename);
        $publicPath = public_path('uploads/avatars/' . $filename);

        $targetPath = File::exists($privatePath) ? $privatePath : (File::exists($publicPath) ? $publicPath : null);

        if (!$targetPath) {
            abort(404, 'Berkas foto profil biometrik tidak ditemukan.');
        }

        $mimeType = File::mimeType($targetPath) ?: 'image/jpeg';

        return response()->file($targetPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
        ]);
    }
}
