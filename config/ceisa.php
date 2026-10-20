<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Profil Perusahaan Terdaftar CEISA 4.0
    |--------------------------------------------------------------------------
    */
    'company' => [
        'name' => env('COMPANY_NAME', 'PT AGUNG TRANS SOLUSINDO'),
        'nib' => env('COMPANY_NIB', '9120009876543'),
        'npwp' => env('COMPANY_NPWP', '012345678012000'),
        'address' => env('COMPANY_ADDRESS', 'Jl. Pelabuhan Nusantara II No. 12, Tanjung Priok, Jakarta Utara'),
        'default_kode_kantor' => env('COMPANY_KODE_KANTOR', '040300'),
        'id_perusahaan' => env('COMPANY_ID_PERUSAHAAN', '012345'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Gateway & Keamanan CEISA 4.0 DJBC
    |--------------------------------------------------------------------------
    */
    'api' => [
        'env' => env('CEISA_ENV', 'sandbox'),
        'base_url' => env('CEISA_BASE_URL', 'https://apis-gw.beacukai.go.id/v2/openapi'),
        'auth_url' => env('CEISA_AUTH_URL', 'https://apis-gw.beacukai.go.id/v1/openapi-auth/user/login'),
        'refresh_token_url' => env('CEISA_REFRESH_TOKEN_URL', 'https://apis-gw.beacukai.go.id/v1/openapi-auth/user/update-token'),
        'username' => env('CEISA_USERNAME', 'agungtrans_api'),
        'password' => env('CEISA_PASSWORD', 'PasswordCeisa123!'),
        'api_key' => env('CEISA_API_KEY', 'pats-ceisa-mock-key-0403'),
        'application_id' => env('CEISA_APPLICATION_ID', ''),
        'whatsapp_no' => env('CEISA_WHATSAPP_NO', ''),
        'mock_mode' => env('CEISA_MOCK_MODE', true),
        'timeout' => 30, // detik
        'token_cache_ttl' => 240, // 4 menit (token berlaku ~5 menit)
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoint Layanan CEISA 4.0 Open API v2
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'auth' => '/user/login',
        'refresh_token' => '/user/update-token',
        'kirim_dokumen' => '/document',
        'check_schema' => '/document/check',
        'status_dokumen' => '/status', // GET /status/{nomorAju}
        'cetak_pdf' => '/respon/pdf',
        'cetak_formulir' => '/respon/cetak-formulir',
        'kurs' => '/kurs',            // GET /kurs/{kodeValuta}
        'tarif_hs' => '/tarif-hs',    // GET /tarif-hs
    ],

    /*
    |--------------------------------------------------------------------------
    | Referensi Kode Kantor Pabean Utama DJBC
    |--------------------------------------------------------------------------
    */
    'kantor_pabean' => [
        '040300' => 'KPU Bea dan Cukai Tipe A Tanjung Priok',
        '050100' => 'KPU Bea dan Cukai Tipe A Tanjung Perak',
        '010700' => 'KPU Bea dan Cukai Tipe C Soekarno-Hatta',
        '020400' => 'KPPBC TMP Belawan',
        '060100' => 'KPPBC TMP Tanjung Emas Semarang',
        '070100' => 'KPPBC TMP Ngurah Rai Bali',
        '100100' => 'KPPBC TMP B Makassar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Referensi Kodifikasi Kepabeanan Standar
    |--------------------------------------------------------------------------
    */
    'referensi' => [
        'jenis_dokumen' => [
            '20' => 'BC 2.0 - Pemberitahuan Impor Barang (PIB)',
            '30' => 'BC 3.0 - Pemberitahuan Ekspor Barang (PEB)',
        ],
        'kode_entitas' => [
            '1' => 'Importir',
            '2' => 'Eksportir',
            '3' => 'Pengusaha TPB',
            '4' => 'PPJK',
            '7' => 'Pemilik Barang',
            '8' => 'Pembeli Luar Negeri',
            '9' => 'Penjual Luar Negeri / Supplier',
            '10' => 'Pemusatan / Vendor',
            '11' => 'Pengirim Luar Negeri',
            '12' => 'Penerima Luar Negeri',
        ],
        'jenis_identitas' => [
            '5' => 'NPWP 15 Digit',
            '6' => 'NPWP 16 Digit / NIK',
            '0' => 'Paspor / Registrasi Luar Negeri',
            '1' => 'KTP',
            '2' => 'Paspor WNI',
        ],
        'cara_bayar' => [
            '1' => 'Biasa / Tunai',
            '2' => 'Berkala',
            '3' => 'Jaminan',
        ],
        'cara_angkut' => [
            '1' => 'Laut',
            '2' => 'Kereta Api',
            '3' => 'Darat',
            '4' => 'Udara',
            '5' => 'Pos',
            '9' => 'Lainnya',
        ],
        'dokumen_pelengkap' => [
            '380' => 'Invoice Komersial',
            '217' => 'Packing List',
            '705' => 'Bill of Lading (B/L)',
            '740' => 'Air Waybill (AWB)',
            '861' => 'Certificate of Origin (COO / Form D/E/AK)',
            '911' => 'Surat Keputusan Fasilitas / Pembebasan',
            '999' => 'Dokumen Lainnya',
        ],
    ],
];
