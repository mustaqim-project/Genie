<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Services\Ceisa\CeisaAuthService;
use App\Services\Ceisa\CeisaClientService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CeisaConfigController extends Controller implements HasMiddleware
{
    protected CeisaAuthService $authService;
    protected CeisaClientService $clientService;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:setting.view', only: ['index']),
            new Middleware('permission:setting.manage', only: ['testAuth']),
        ];
    }

    public function __construct(CeisaAuthService $authService, CeisaClientService $clientService)
    {
        $this->authService = $authService;
        $this->clientService = $clientService;
    }

    public function index()
    {
        $config = config('ceisa');
        $logs = CeisaApiLog::latest()->paginate(15);

        return view('config.index', compact('config', 'logs'));
    }

    public function testAuth()
    {
        try {
            $this->authService->clearToken();
            $token = $this->authService->getAccessToken();

            return back()->with('success', 'Koneksi ke Otorisasi OAuth CEISA 4.0 BERHASIL! Token: ' . substr($token, 0, 25) . '...');
        } catch (\Throwable $e) {
            return back()->with('error', 'Koneksi GAGAL: ' . $e->getMessage());
        }
    }
}
