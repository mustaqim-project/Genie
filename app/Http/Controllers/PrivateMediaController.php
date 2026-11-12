<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateMediaController extends Controller
{
    /**
     * Stream a private biometric face image if authenticated and valid signed URL.
     */
    public function showBiometric(Request $request, string $employeeId)
    {
        // Enforce valid signature (or super admin / owner bypass if logged in)
        if (! $request->hasValidSignature()) {
            if (! auth()->check() || ! in_array(auth()->user()->role ?? '', ['R-OWN', 'R-MGR', 'R-SYS'])) {
                abort(403, 'Akses gambar biometrik ditolak: Tautan kedaluwarsa atau tidak sah (UU PDP No. 27/2022).');
            }
        }

        $employee = Employee::findOrFail($employeeId);

        // Security scoping: only owner, HR manager, or the employee himself can view
        if (auth()->check()) {
            $user = auth()->user();
            $isSelf = ($employee->user_id === $user->id);
            $isPrivileged = in_array($user->role ?? '', ['R-OWN', 'R-MGR', 'R-SYS']);
            if (! $isSelf && ! $isPrivileged) {
                abort(403, 'Akses dibatasi hanya untuk pemilik data atau staf kepatuhan.');
            }
        }

        $path = $employee->photo_url;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            // Fallback: check if stored under private/biometrics/{employeeId}.jpg
            $fallback = 'private/biometrics/' . $employee->id . '.jpg';
            if (Storage::disk('local')->exists($fallback)) {
                $path = $fallback;
            } else {
                abort(404, 'Berkas foto biometrik tidak ditemukan.');
            }
        }

        $mimeType = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';

        return response()->stream(function () use ($path) {
            $stream = Storage::disk('local')->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="biometric_' . $employee->nik . '.jpg"',
            'Cache-Control' => 'no-cache, private, max-age=300',
        ]);
    }

    /**
     * Stream private attendance clock-in selfie.
     */
    public function showAttendancePhoto(Request $request, string $attendanceLogId)
    {
        if (! $request->hasValidSignature()) {
            if (! auth()->check() || ! in_array(auth()->user()->role ?? '', ['R-OWN', 'R-MGR', 'R-SYS'])) {
                abort(403, 'Tautan verifikasi presensi tidak sah.');
            }
        }

        $log = AttendanceLog::findOrFail($attendanceLogId);
        $path = $log->photo_url;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'Foto presensi tidak ditemukan.');
        }

        $mimeType = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';

        return response()->stream(function () use ($path) {
            $stream = Storage::disk('local')->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-cache, private, max-age=300',
        ]);
    }
}
