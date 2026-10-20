<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BC20Controller;
use App\Http\Controllers\BC30Controller;
use App\Http\Controllers\Api\CeisaApiController;
use App\Http\Controllers\CeisaConfigController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\HrmController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffPortalController;
use App\Http\Controllers\TrafficController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/login/otp', [AuthController::class, 'showOtpForm'])->name('login.otp');
    Route::post('/login/otp', [AuthController::class, 'verifyOtp'])->name('login.otp.post');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Quick Role Switcher (Bisa diakses untuk testing simulasi RBAC)
Route::get('/quick-login/{roleCode}', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Portal Mandiri Klien (Pelacakan Kargo & Upload Dokumen Mandiri)
Route::get('/portal/{token?}', [\App\Http\Controllers\ClientPortalController::class, 'index'])->name('portal.index');
Route::post('/portal/{shipmentId}/upload', [\App\Http\Controllers\ClientPortalController::class, 'uploadDocument'])->name('portal.upload');

// Protected Routes (Wajib Login & RBAC Active)
Route::middleware('auth')->group(function () {
    // 0. Profil Pengguna & Keamanan Akun
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // 1. Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Core Bisnis - Tim Document (BC 2.0 & BC 3.0)
    Route::prefix('bc-20')->name('bc20.')->group(function () {
        Route::get('/', [BC20Controller::class, 'index'])->name('index');
        Route::get('/create', [BC20Controller::class, 'create'])->name('create');
        Route::post('/', [BC20Controller::class, 'store'])->name('store');
        Route::get('/{bc20}', [BC20Controller::class, 'show'])->name('show');
        Route::post('/{bc20}/check-schema', [BC20Controller::class, 'checkSchema'])->name('check-schema');
        Route::post('/{bc20}/submit', [BC20Controller::class, 'submit'])->name('submit');
        Route::post('/{bc20}/check-status', [BC20Controller::class, 'checkStatus'])->name('check-status');
        Route::post('/{bc20}/fetch-response', [BC20Controller::class, 'fetchResponse'])->name('fetch-response');
    });

    Route::prefix('bc-30')->name('bc30.')->group(function () {
        Route::get('/', [BC30Controller::class, 'index'])->name('index');
        Route::get('/create', [BC30Controller::class, 'create'])->name('create');
        Route::post('/', [BC30Controller::class, 'store'])->name('store');
        Route::get('/{bc30}', [BC30Controller::class, 'show'])->name('show');
        Route::post('/{bc30}/check-schema', [BC30Controller::class, 'checkSchema'])->name('check-schema');
        Route::post('/{bc30}/submit', [BC30Controller::class, 'submit'])->name('submit');
        Route::post('/{bc30}/check-status', [BC30Controller::class, 'checkStatus'])->name('check-status');
        Route::post('/{bc30}/fetch-response', [BC30Controller::class, 'fetchResponse'])->name('fetch-response');
    });

    // Kurs Kepabeanan & Tarif HS Code Real-Time
    Route::get('/ceisa/kurs/{valuta}', [CeisaApiController::class, 'getKurs'])->name('ceisa.kurs');
    Route::get('/ceisa/tarif-hs/{hsCode}', [CeisaApiController::class, 'getTarifHs'])->name('ceisa.tarif-hs');

    // 2. Core Bisnis - Tim DO & Tim Traffic
    Route::prefix('do')->name('do.')->group(function () {
        Route::get('/', [DoController::class, 'index'])->name('index');
        Route::get('/{id}', [DoController::class, 'show'])->name('show');
    });

    Route::prefix('traffic')->name('traffic.')->group(function () {
        Route::get('/', [TrafficController::class, 'index'])->name('index');
        Route::get('/{id}', [TrafficController::class, 'show'])->name('show');
    });

    // 2.5 Staff Personal Portal & Attendance Hub (Employee Self-Service / ESS)
    Route::get('/hrm/portal', [StaffPortalController::class, 'index'])->name('hrm.portal');
    Route::get('/my-portal', fn() => redirect()->route('hrm.portal'))->name('my-portal');
    Route::post('/hrm/portal/clock-in', [StaffPortalController::class, 'clockIn'])->name('hrm.portal.clock-in');
    Route::post('/hrm/portal/clock-out', [StaffPortalController::class, 'clockOut'])->name('hrm.portal.clock-out');
    Route::post('/hrm/portal/corrections', [StaffPortalController::class, 'storeCorrection'])->name('hrm.portal.corrections.store');
    Route::post('/hrm/portal/leaves', [StaffPortalController::class, 'storeLeave'])->name('hrm.portal.leaves.store');
    Route::post('/hrm/portal/cash-advances', [StaffPortalController::class, 'storeCashAdvance'])->name('hrm.portal.cash-advances.store');
    Route::post('/hrm/portal/face-register', [StaffPortalController::class, 'registerFace'])->name('hrm.portal.face-register');
    Route::post('/hrm/portal/face-verify', [StaffPortalController::class, 'verifyFace'])->name('hrm.portal.face-verify');
    Route::get('/hrm/portal/payslips/{payslip}', [StaffPortalController::class, 'showPayslip'])->name('hrm.portal.payslips.show');

    // 3. HRM (Kepegawaian, Presensi Geofencing GPS & Wajah, Cuti, Payroll, Aset)
    Route::prefix('hrm')->name('hrm.')->group(function () {
        Route::get('/', [HrmController::class, 'index'])->name('index');

        // Pegawai & Kontrak
        Route::get('/employees/export', [HrmController::class, 'exportEmployees'])->name('employees.export');
        Route::get('/employees/template', [HrmController::class, 'employeeTemplate'])->name('employees.template');
        Route::post('/employees/import/preview', [HrmController::class, 'previewEmployeeImport'])->name('employees.import-preview');
        Route::post('/employees/import/execute', [HrmController::class, 'executeEmployeeImport'])->name('employees.import-execute');
        Route::post('/employees', [HrmController::class, 'storeEmployee'])->name('employees.store');
        Route::get('/employees/{employee}', [HrmController::class, 'showEmployee'])->name('employees.show');
        Route::put('/employees/{employee}', [HrmController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{employee}', [HrmController::class, 'destroyEmployee'])->name('employees.destroy');

        // Presensi & Geofencing GPS Wajah UU PDP
        Route::get('/attendance/export', [HrmController::class, 'exportAttendance'])->name('attendance.export');
        Route::get('/attendance/template', [HrmController::class, 'attendanceTemplate'])->name('attendance.template');
        Route::post('/attendance/import/preview', [HrmController::class, 'previewAttendanceImport'])->name('attendance.import-preview');
        Route::post('/attendance/import/execute', [HrmController::class, 'executeAttendanceImport'])->name('attendance.import-execute');
        Route::post('/attendance/clock', [HrmController::class, 'clockAttendance'])->name('attendance.clock');
        Route::post('/attendance/consent', [HrmController::class, 'storeConsent'])->name('attendance.consent');

        // Koreksi Presensi (Attendance Corrections)
        Route::post('/attendance/corrections', [HrmController::class, 'storeCorrection'])->name('attendance.corrections.store');
        Route::post('/attendance/corrections/{correction}/approve', [HrmController::class, 'approveCorrection'])->name('attendance.corrections.approve');
        Route::post('/attendance/corrections/{correction}/reject', [HrmController::class, 'rejectCorrection'])->name('attendance.corrections.reject');

        // Cuti & Alih Tugas Otomatis PIC Forwarder
        Route::post('/leaves', [HrmController::class, 'storeLeave'])->name('leaves.store');
        Route::post('/leaves/{leaveRequest}/approve', [HrmController::class, 'approveLeave'])->name('leaves.approve');
        Route::post('/leaves/{leaveRequest}/reject', [HrmController::class, 'rejectLeave'])->name('leaves.reject');

        // Lembur & Kasbon
        Route::post('/overtime', [HrmController::class, 'storeOvertime'])->name('overtime.store');
        Route::post('/overtime/{overtimeRequest}/approve', [HrmController::class, 'approveOvertime'])->name('overtime.approve');
        Route::post('/overtime/{overtimeRequest}/reject', [HrmController::class, 'rejectOvertime'])->name('overtime.reject');
        Route::post('/cash-advances', [HrmController::class, 'storeCashAdvance'])->name('cash-advances.store');
        Route::post('/loans/{loan}/cancel', [HrmController::class, 'cancelLoan'])->name('loans.cancel');

        // Payroll Engine
        Route::get('/payrolls/create', [HrmController::class, 'createPayroll'])->name('payrolls.create');
        Route::post('/payrolls', [HrmController::class, 'storePayroll'])->name('payrolls.store');
        Route::get('/payrolls/{payrollRun}', [HrmController::class, 'showPayroll'])->name('payrolls.show');
        Route::get('/payrolls/{payrollRun}/export-excel', [HrmController::class, 'exportPayrollExcel'])->name('payrolls.export-excel');
        Route::get('/payrolls/{payrollRun}/export-bank', [HrmController::class, 'exportPayrollBankCsv'])->name('payrolls.export-bank');
        Route::get('/payslips/{payslip}/print', [HrmController::class, 'printPayslip'])->name('payslips.print');
        Route::post('/payroll/generate', [HrmController::class, 'generatePayrollRun'])->name('payroll.generate');
        Route::post('/payroll/{payrollRun}/approve', [HrmController::class, 'approvePayrollRun'])->name('payroll.approve');
        Route::post('/payroll/{payrollRun}/disburse', [HrmController::class, 'disbursePayrollRun'])->name('payroll.disburse');
        Route::get('/payroll/payslips/{payslip}', [HrmController::class, 'showPayslip'])->name('payroll.payslips.show');

        // Aset & Inventaris Perusahaan
        Route::post('/assets', [HrmController::class, 'storeAsset'])->name('assets.store');
        Route::post('/assets/{asset}/assign', [HrmController::class, 'assignAsset'])->name('assets.assign');
        Route::post('/assets/assignments/{assignment}/return', [HrmController::class, 'returnAsset'])->name('assets.return');

        // Lokasi Geofence & Shift & Penjadwalan
        Route::post('/locations', [HrmController::class, 'storeLocation'])->name('locations.store');
        Route::put('/locations/{location}', [HrmController::class, 'updateLocation'])->name('locations.update');
        Route::delete('/locations/{location}', [HrmController::class, 'destroyLocation'])->name('locations.destroy');

        Route::post('/shifts', [HrmController::class, 'storeShift'])->name('shifts.store');
        Route::put('/shifts/{shift}', [HrmController::class, 'updateShift'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [HrmController::class, 'destroyShift'])->name('shifts.destroy');

        Route::post('/schedules', [HrmController::class, 'storeSchedule'])->name('schedules.store');
        Route::delete('/schedules/{schedule}', [HrmController::class, 'destroySchedule'])->name('schedules.destroy');
    });

    // 4. CRM & Sales Pipeline
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/', [CrmController::class, 'index'])->name('index');
    });

    // 4.1 Kotak Masuk Terpadu (Unified Omnichannel Inbox: WhatsApp + Social Media)
    Route::prefix('inbox')->name('inbox.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InboxController::class, 'index'])->name('index');
        Route::post('/whatsapp/send', [\App\Http\Controllers\InboxController::class, 'sendWhatsAppMessage'])->name('whatsapp.send');
        Route::post('/social/{id}/reply', [\App\Http\Controllers\InboxController::class, 'replySocialInteraction'])->name('social.reply');
    });

    // 4.2 WhatsApp Business (Meta WABA Official Gateway)
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WhatsAppController::class, 'index'])->name('index');
        Route::post('/broadcasts', [\App\Http\Controllers\WhatsAppController::class, 'storeBroadcast'])->name('broadcasts.store');
        Route::post('/templates', [\App\Http\Controllers\WhatsAppController::class, 'storeTemplate'])->name('templates.store');
        Route::post('/test', [\App\Http\Controllers\WhatsAppController::class, 'sendTest'])->name('test');
    });

    // 4.3 Media Sosial (Multi-Channel Publishing & Composer)
    Route::prefix('social-media')->name('social-media.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SocialMediaController::class, 'index'])->name('index');
        Route::post('/posts', [\App\Http\Controllers\SocialMediaController::class, 'storePost'])->name('posts.store');
        Route::post('/posts/{post}/publish-now', [\App\Http\Controllers\SocialMediaController::class, 'publishNow'])->name('posts.publish-now');
        Route::patch('/posts/{post}/reschedule', [\App\Http\Controllers\SocialMediaController::class, 'reschedule'])->name('posts.reschedule');
        Route::delete('/posts/{post}', [\App\Http\Controllers\SocialMediaController::class, 'destroyPost'])->name('posts.destroy');
    });

    // 5. Finance
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
    });

    // 6. Accounting
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/', [AccountingController::class, 'index'])->name('index');
        Route::get('/export-ledger', [AccountingController::class, 'exportLedger'])->name('export-ledger');
    });

    // 7. AI Agent & MCP Integration Hub
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AiAgentController::class, 'index'])->name('index');
    });
    Route::get('/settings/integrations/mcp', [\App\Http\Controllers\McpIntegrationController::class, 'index'])->name('settings.mcp');
    Route::post('/settings/integrations/mcp/tokens', [\App\Http\Controllers\McpIntegrationController::class, 'storeToken'])->name('settings.mcp.tokens.store');
    Route::delete('/settings/integrations/mcp/tokens/{id}', [\App\Http\Controllers\McpIntegrationController::class, 'revokeToken'])->name('settings.mcp.tokens.revoke');
    Route::get('/mcp', [\App\Http\Controllers\McpIntegrationController::class, 'index'])->name('mcp.hub');

    // 8. Manajemen Peran & Hak Akses (RBAC Cockpit) - Paritas Cooca Core
    Route::prefix('settings/roles')->name('settings.roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        Route::post('/members', [RoleController::class, 'storeMember'])->name('members.store');
        Route::put('/members/{user}', [RoleController::class, 'updateMemberRole'])->name('members.role');
        Route::delete('/members/{user}', [RoleController::class, 'destroyMember'])->name('members.destroy');
    });
    Route::get('/settings/roles-matrix', [RoleController::class, 'index'])->name('settings.roles');

    // 9. Setting & Konfigurasi Gateway (RBAC & User Management)
    Route::prefix('setting')->name('setting.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/users', [SettingController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [SettingController::class, 'updateUser'])->name('users.update');
        Route::post('/user/{user}/role', [SettingController::class, 'updateUserRole'])->name('user-role');
        Route::post('/users/{user}/toggle-status', [SettingController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [SettingController::class, 'resetUserPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [SettingController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/roles/{role}/permissions', [SettingController::class, 'updateRolePermissions'])->name('roles.permissions');

        // Centralized Settings for WhatsApp WABA & Social Media Official Channels
        Route::post('/whatsapp', [SettingController::class, 'updateWaSettings'])->name('whatsapp.update');
        Route::post('/social/connect', [SettingController::class, 'connectSocialAccount'])->name('social.connect');
        Route::post('/social/{id}/disconnect', [SettingController::class, 'disconnectSocialAccount'])->name('social.disconnect');
    });

    Route::prefix('config')->name('config.')->group(function () {
        Route::get('/', [CeisaConfigController::class, 'index'])->name('index');
        Route::post('/test-auth', [CeisaConfigController::class, 'testAuth'])->name('test-auth');
        Route::post('/sync-hs-codes', [CeisaConfigController::class, 'syncHsCodes'])->name('sync-hs-codes');
        Route::post('/test-hs', [CeisaConfigController::class, 'testHsCode'])->name('test-hs');
    });

    // ==========================================
    // Core Bisnis End-to-End Workflow Routes
    // ==========================================
    // Tim Document Project Lifecycle (P01 - P19)
    Route::prefix('core/document')->name('core.document.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ShipmentProjectController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\ShipmentProjectController::class, 'store'])->name('store');
        Route::post('/parse-documents', [\App\Http\Controllers\ShipmentProjectController::class, 'parseDocuments'])->name('parse-documents');
        Route::get('/{id}', [\App\Http\Controllers\ShipmentProjectController::class, 'show'])->name('show');
        Route::post('/{id}/sync-from-docs', [\App\Http\Controllers\ShipmentProjectController::class, 'reSyncFromDocs'])->name('sync-from-docs');
        Route::post('/{id}/upload-document', [\App\Http\Controllers\ShipmentProjectController::class, 'uploadDocument'])->name('upload-document');
        Route::get('/{id}/download-doc/{docId}', [\App\Http\Controllers\ShipmentProjectController::class, 'downloadDocument'])->name('download-doc');
        Route::post('/{id}/verify-document/{docId}', [\App\Http\Controllers\ShipmentProjectController::class, 'verifyDocument'])->name('verify-document');
        Route::post('/{id}/verify-all', [\App\Http\Controllers\ShipmentProjectController::class, 'verifyAllDocuments'])->name('verify-all');
        Route::post('/{id}/item-document/store', [\App\Http\Controllers\ShipmentProjectController::class, 'addItemCustomsDocument'])->name('item-document.store');
        Route::post('/{id}/item-document/{docId}', [\App\Http\Controllers\ShipmentProjectController::class, 'updateItemDocument'])->name('item-document.update');
        Route::delete('/{id}/item-document/{docId}', [\App\Http\Controllers\ShipmentProjectController::class, 'deleteItemDocument'])->name('item-document.destroy');
        Route::post('/{id}/item-document/{docId}/verify', [\App\Http\Controllers\ShipmentProjectController::class, 'verifyItemDocument'])->name('item-document.verify');
        Route::post('/{id}/detect-hs', [\App\Http\Controllers\ShipmentProjectController::class, 'detectHsCode'])->name('detect-hs');
        Route::post('/{id}/items', [\App\Http\Controllers\ShipmentProjectController::class, 'addItem'])->name('items.store');
        Route::put('/{id}/items/{itemId}', [\App\Http\Controllers\ShipmentProjectController::class, 'updateItem'])->name('items.update');
        Route::delete('/{id}/items/{itemId}', [\App\Http\Controllers\ShipmentProjectController::class, 'deleteItem'])->name('items.destroy');
        Route::get('/{id}/verify-packing-list', [\App\Http\Controllers\ShipmentProjectController::class, 'verifyPackingList'])->name('verify-packing-list');
        Route::post('/{id}/verify-packing-list', [\App\Http\Controllers\ShipmentProjectController::class, 'verifyPackingList'])->name('verify-packing-list.post');
        Route::get('/{id}/generate-pib-peb', [\App\Http\Controllers\ShipmentProjectController::class, 'generatePibPeb'])->name('generate-pib-peb');
        Route::post('/{id}/generate-pib-peb', [\App\Http\Controllers\ShipmentProjectController::class, 'generatePibPeb'])->name('generate-pib-peb.post');
        Route::post('/{id}/submit-ceisa', [\App\Http\Controllers\ShipmentProjectController::class, 'generateAndSubmitCeisa'])->name('submit-ceisa');
    });

    // Tim DO Lifecycle & Shipping Line Automation
    Route::prefix('core/do')->name('core.do.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DoWorkflowController::class, 'index'])->name('index');
        Route::post('/{id}/send-email', [\App\Http\Controllers\DoWorkflowController::class, 'sendVendorEmail'])->name('send-email');
        Route::post('/{id}/release-do', [\App\Http\Controllers\DoWorkflowController::class, 'releaseDo'])->name('release-do');
    });

    // Tim Traffic Lifecycle, Behandle & Gate Out
    Route::prefix('core/traffic')->name('core.traffic.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TrafficWorkflowController::class, 'index'])->name('index');
        Route::post('/{id}/customs-status', [\App\Http\Controllers\TrafficWorkflowController::class, 'updateCustomsStatus'])->name('customs-status');
        Route::post('/{id}/gate-out', [\App\Http\Controllers\TrafficWorkflowController::class, 'gateOut'])->name('gate-out');
        Route::post('/{id}/deliver-pod', [\App\Http\Controllers\TrafficWorkflowController::class, 'deliverPod'])->name('deliver-pod');
    });

    // Finance Invoices (P19)
    Route::prefix('finance/invoices')->name('finance.invoices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ClientInvoiceController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\ClientInvoiceController::class, 'show'])->name('show');
        Route::post('/{id}/pay', [\App\Http\Controllers\ClientInvoiceController::class, 'markAsPaid'])->name('pay');
    });

    // Finance Bills (AP - Hutang Vendor Pelayaran, Trucking, TPS)
    Route::prefix('finance/bills')->name('finance.bills.')->group(function () {
        Route::get('/', [\App\Http\Controllers\VendorBillController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\VendorBillController::class, 'store'])->name('store');
        Route::post('/{id}/approve', [\App\Http\Controllers\VendorBillController::class, 'approve'])->name('approve');
        Route::post('/{id}/pay', [\App\Http\Controllers\VendorBillController::class, 'pay'])->name('pay');
    });

    // Finance Cash & Bank (Buku Kas, Mutasi & Pindah Buku)
    Route::prefix('finance/cash-bank')->name('finance.cash-bank.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CashBankController::class, 'index'])->name('index');
        Route::post('/accounts', [\App\Http\Controllers\CashBankController::class, 'storeAccount'])->name('accounts.store');
        Route::post('/transactions', [\App\Http\Controllers\CashBankController::class, 'storeTransaction'])->name('transactions.store');
        Route::post('/transfer', [\App\Http\Controllers\CashBankController::class, 'transfer'])->name('transfer');
        Route::post('/transactions/{id}/reconcile', [\App\Http\Controllers\CashBankController::class, 'reconcile'])->name('transactions.reconcile');
    });

    // Finance Tax (Perpajakan PPN 1.1% PMK 71/2022 & PPh 23)
    Route::prefix('finance/tax')->name('finance.tax.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TaxController::class, 'index'])->name('index');
        Route::get('/export-efaktur', [\App\Http\Controllers\TaxController::class, 'exportEfaktur'])->name('export');
    });

    // CRM Quotations (Penawaran Harga)
    Route::prefix('crm/quotations')->name('crm.quotations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\QuotationController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\QuotationController::class, 'store'])->name('store');
        Route::post('/{id}/status', [\App\Http\Controllers\QuotationController::class, 'updateStatus'])->name('status');
        Route::post('/{id}/convert-to-project', [\App\Http\Controllers\QuotationController::class, 'convertToProject'])->name('convert-to-project');
    });

    // ==========================================
    // Template PDF & Cetak Resmi Routes
    // ==========================================
    Route::prefix('print')->name('pdf.')->group(function () {
        Route::get('/shipments/{id}/pib', [\App\Http\Controllers\PdfController::class, 'pib'])->name('pib');
        Route::get('/shipments/{id}/peb', [\App\Http\Controllers\PdfController::class, 'peb'])->name('peb');
        Route::get('/shipments/{id}/do-request', [\App\Http\Controllers\PdfController::class, 'doRequest'])->name('do-request');
        Route::get('/shipments/{id}/billing', [\App\Http\Controllers\PdfController::class, 'billingMpn'])->name('billing');
        Route::get('/invoices/{id}', [\App\Http\Controllers\PdfController::class, 'invoice'])->name('invoice');
        Route::get('/quotations/{id}', [\App\Http\Controllers\PdfController::class, 'quotation'])->name('quotation');
    });
});
