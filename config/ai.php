<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI-Compatible Customs AI Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi gateway AI OpenAI-compatible untuk pemindaian Packing List,
    | pencocokan pos tarif HS Code BTKI 2022, audit izin lartas Bea Cukai,
    | dan deklarasi pabean CEISA 4.0 (PIB BC 2.0 & PEB BC 3.0).
    |
    */

    'endpoint' => env('OPENAI_CUSTOMS_ENDPOINT', 'https://r4g77gv.abc-tunnel.us/v1'),
    'api_key' => env('OPENAI_CUSTOMS_API_KEY', 'sk-59807b51bc76063d-ge0agt-97ac1590'),
    'default_model' => env('OPENAI_CUSTOMS_MODEL', 'cx/gpt-6-astra'),
    'timeout' => env('OPENAI_CUSTOMS_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Supported AI Models
    |--------------------------------------------------------------------------
    |
    | Daftar seluruh model AI yang didukung sistem GENIE.
    |
    */

    'models' => [
        // OpenAI Codex Astra Series (Top Tier Reasoning & Classification)
        'cx/gpt-6-astra-xhigh' => [
            'id' => 'cx/gpt-6-astra-xhigh',
            'name' => 'GPT-6 Astra (Extra High Reasoning)',
            'provider' => 'OpenAI Codex',
            'series' => 'Astra',
            'badge' => 'Ultra Precision',
            'description' => 'Akurasi maksimal untuk klasifikasi barang teknis rumit, kimia, dan multi-komoditas.',
            'recommended' => false,
        ],
        'cx/gpt-6-astra' => [
            'id' => 'cx/gpt-6-astra',
            'name' => 'GPT-6 Astra (Balanced & Fast)',
            'provider' => 'OpenAI Codex',
            'series' => 'Astra',
            'badge' => 'Recommended',
            'description' => 'Model utama berkecepatan tinggi dengan pemahaman mendalam BTKI 2022.',
            'recommended' => true,
        ],

        // OpenAI Codex Sol Series
        'cx/gpt-5.6-sol-xhigh' => [
            'id' => 'cx/gpt-5.6-sol-xhigh',
            'name' => 'GPT-5.6 Sol (Extra High)',
            'provider' => 'OpenAI Codex',
            'series' => 'Sol',
            'badge' => 'High Reasoning',
            'description' => 'Penalaran tingkat lanjut untuk dokumen manufaktur dan permesinan berat.',
            'recommended' => false,
        ],
        'cx/gpt-5.6-sol' => [
            'id' => 'cx/gpt-5.6-sol',
            'name' => 'GPT-5.6 Sol (Standard)',
            'provider' => 'OpenAI Codex',
            'series' => 'Sol',
            'badge' => 'Stable',
            'description' => 'Ekstraksi stabil untuk packing list standar industri.',
            'recommended' => false,
        ],

        // OpenAI Codex Terra & Luna Series
        'cx/gpt-5.6-terra' => [
            'id' => 'cx/gpt-5.6-terra',
            'name' => 'GPT-5.6 Terra (Customs Specialized)',
            'provider' => 'OpenAI Codex',
            'series' => 'Terra',
            'badge' => 'Customs Specialized',
            'description' => 'Optimalisasi untuk komoditas kepabeanan, logistik, dan perdagangan internasional.',
            'recommended' => false,
        ],
        'cx/gpt-5.6-luna' => [
            'id' => 'cx/gpt-5.6-luna',
            'name' => 'GPT-5.6 Luna (Lightweight)',
            'provider' => 'OpenAI Codex',
            'series' => 'Luna',
            'badge' => 'Fast',
            'description' => 'Model cepat untuk packing list sederhana dan pengecekan cepat.',
            'recommended' => false,
        ],

        // OpenAI Codex 5.x Series
        'cx/gpt-5.5-xhigh' => [
            'id' => 'cx/gpt-5.5-xhigh',
            'name' => 'GPT-5.5 (Extra High)',
            'provider' => 'OpenAI Codex',
            'series' => 'GPT-5.5',
            'badge' => 'Legacy High',
            'description' => 'Seri GPT-5.5 penalaran tinggi.',
            'recommended' => false,
        ],
        'cx/gpt-5.5' => [
            'id' => 'cx/gpt-5.5',
            'name' => 'GPT-5.5 (Standard)',
            'provider' => 'OpenAI Codex',
            'series' => 'GPT-5.5',
            'badge' => 'Legacy',
            'description' => 'Model GPT-5.5 standar.',
            'recommended' => false,
        ],
        'cx/gpt-5.4' => [
            'id' => 'cx/gpt-5.4',
            'name' => 'GPT-5.4 (Core)',
            'provider' => 'OpenAI Codex',
            'series' => 'GPT-5.4',
            'badge' => 'Legacy',
            'description' => 'Model GPT-5.4 core.',
            'recommended' => false,
        ],

        // Google Antigravity Gemini Series
        'ag/gemini-3.8-flash-high' => [
            'id' => 'ag/gemini-3.8-flash-high',
            'name' => 'Gemini 3.8 Flash (High Reasoning)',
            'provider' => 'Google Antigravity',
            'series' => 'Gemini',
            'badge' => 'Multimodal High',
            'description' => 'Pemrosesan konteks besar dengan penalaran analitis tajam.',
            'recommended' => false,
        ],
        'ag/gemini-3.8-flash-medium' => [
            'id' => 'ag/gemini-3.8-flash-medium',
            'name' => 'Gemini 3.8 Flash (Medium)',
            'provider' => 'Google Antigravity',
            'series' => 'Gemini',
            'badge' => 'Multimodal Med',
            'description' => 'Keseimbangan kecepatan dan analisis dokumen.',
            'recommended' => false,
        ],
        'ag/gemini-3.8-flash-low' => [
            'id' => 'ag/gemini-3.8-flash-low',
            'name' => 'Gemini 3.8 Flash (Low / Ultra Fast)',
            'provider' => 'Google Antigravity',
            'series' => 'Gemini',
            'badge' => 'Ultra Fast',
            'description' => 'Respon kilat untuk verifikasi cepat pos tarif tunggal.',
            'recommended' => false,
        ],
        'ag/gemini-3.7-flash-high' => [
            'id' => 'ag/gemini-3.7-flash-high',
            'name' => 'Gemini 3.7 Flash (High)',
            'provider' => 'Google Antigravity',
            'series' => 'Gemini',
            'badge' => 'Multimodal High',
            'description' => 'Model penalaran tinggi Gemini generasi 3.7.',
            'recommended' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Models Order
    |--------------------------------------------------------------------------
    |
    | Jika model yang dipilih mengalami keterbatasan jaringan atau kuota,
    | urutan fallback otomatis berikut akan dieksekusi secara transparan.
    |
    */
    'fallback_models' => [
        'cx/gpt-6-astra',
        'cx/gpt-5.6-terra',
        'cx/gpt-6-astra-xhigh',
        'cx/gpt-5.6-sol',
        'cx/gpt-5.6-luna',
        'ag/gemini-3.8-flash-high',
        'ag/gemini-3.8-flash-low',
    ],
];
