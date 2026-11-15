<?php

namespace App\Services\Hrm;

use App\Models\Employee;

/**
 * Service verifikasi biometrik wajah (UU PDP No. 27/2022).
 * Mewarisi implementasi matematika FaceVerificationService (Cosine Similarity 128-D).
 */
class BiometricVerificationService extends FaceVerificationService
{
    // Alias / backward compatibility
}
