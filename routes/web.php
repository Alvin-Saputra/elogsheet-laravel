<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MstRoleController;
use App\Http\Controllers\MstUserController;
use App\Http\Controllers\MstPlantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RptQualityController;
use App\Http\Controllers\RptDailyPFraController;
use App\Http\Controllers\RptDailyPRefController;
use App\Http\Controllers\RptLampGlassController;
use App\Http\Controllers\LogsheetDryFraController;
use App\Http\Controllers\MstMastervalueController;
use App\Http\Controllers\RptDeodorizingController;
use App\Http\Controllers\RptLogsheetPBFController;
use App\Http\Controllers\MstBusinessUnitController;
use App\Http\Controllers\RptChangeProductController;
use App\Http\Controllers\RptLogsheetDryFraController;
use App\Http\Controllers\RptDailyProductionController;
use App\Http\Controllers\RptStartupProduksiController;
/*
|--------------------------------------------------------------------------
| Guest Routes (Login)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\RptDailyStorageTankAnalytical;
use App\Http\Controllers\RptDailyStorageTankAnalyticalController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('root');


Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */
    Route::resource('business-unit', MstBusinessUnitController::class);
    Route::resource('master-role', MstRoleController::class);
    Route::resource('master-plant', MstPlantController::class);
    Route::resource('user', MstUserController::class)->only(['index']);
    Route::resource('master-value', MstMastervalueController::class);


    /*
|--------------------------------------------------------------------------
| Report: F/RFA-001 - Quality Report
|--------------------------------------------------------------------------
*/

    // ================= PRODUKSI =================
    Route::prefix('report-quality')->name('report-quality.')->group(function () {
        // index
        Route::get('/', [RptQualityController::class, 'index'])->name('index');

        // detail
        Route::get('/{id}', [RptQualityController::class, 'show'])->name('show');

        // approve / reject per tanggal
        Route::post('/approve-date', [RptQualityController::class, 'approveDate'])->name('approve-date');
        Route::post('/reject-date', [RptQualityController::class, 'rejectDate'])->name('reject-date');

        // approve / reject per tiket
        Route::post('/{id}/approve', [RptQualityController::class, 'approveTicket'])->name('approve');
        Route::post('/{id}/reject', [RptQualityController::class, 'rejectTicket'])->name('reject');

        // export
        Route::get('/export/view', [RptQualityController::class, 'exportLayoutPreview'])->name('export.view');
        Route::get('/export/excel', [RptQualityController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [RptQualityController::class, 'exportPdf'])->name('export.pdf');
    });

    // ================= QC =================
    Route::prefix('report-quality-qc')->name('report-quality.qc.')->group(function () {
        // index
        Route::get('/', [RptQualityController::class, 'indexQc'])->name('index');

        // detail
        Route::get('/{id}', [RptQualityController::class, 'showQc'])->name('show');

        // approve / reject per tanggal
        Route::post('/approve-date', [RptQualityController::class, 'approveDateQc'])->name('approve-date');
        Route::post('/reject-date', [RptQualityController::class, 'rejectDateQc'])->name('reject-date');

        // approve / reject per tiket
        Route::post('/{id}/approve', [RptQualityController::class, 'approveTicketQc'])->name('approve');
        Route::post('/{id}/reject', [RptQualityController::class, 'rejectTicketQc'])->name('reject');

        // export
        Route::get('/export/view', [RptQualityController::class, 'exportLayoutPreviewQc'])->name('export.view');
        Route::get('/export/pdf', [RptQualityController::class, 'exportPdfQc'])->name('export.pdf');
    });


    /*
    |--------------------------------------------------------------------------
    | Report: F/RFA-002 - Pretreatment Bleaching Filtration
    |--------------------------------------------------------------------------
    */
    // TODO : CHANGE THE CONTROLLERS
    // Route::prefix('pretreatment-bleaching-filtration')->name('pretreatment-bleaching-filtration')->group(function () {
    //     Route::get('/', [RptLogsheetPBFController::class, 'index'], )->name('index');

    //     Route::get('/{id}', [RptLogsheetPBFController::class, 'showQc'])->name('show');

    //     // approve / reject per tanggal
    //     Route::post('/approve-date', [RptLogsheetPBFController::class, 'approveDate'])->name('approve-date');
    //     Route::post('/reject-date', [RptLogsheetPBFController::class, 'rejectDateQc'])->name('reject-date');

    //     // approve / reject per tiket (prepared)
    //     Route::post('/{id}/approve', [RptLogsheetPBFController::class, 'approveTicket'])->name('approve');
    //     Route::post('/{id}/reject', [RptLogsheetPBFController::class, 'rejectTicket'])->name('reject');

    //     // export
    //     Route::get('/preview', [RptLogsheetPBFController::class, 'exportLayoutPreview'])->name('export.view');
    //     Route::get('/pdf', [RptLogsheetPBFController::class, 'exportPdf'])->name('export.pdf');
    // });

    Route::prefix('report-pretreatment')->name('report-pretreatment.')->group(function () {
        Route::get('/', [RptLogsheetPBFController::class, 'index'])->name('index');
        Route::get('/{id}', [RptLogsheetPBFController::class, 'show'])->name('show');

        // Approve/reject by date
        Route::post('/approve-date', [RptLogsheetPBFController::class, 'approveDate'])->name('approve-date');
        Route::post('/reject-date', [RptLogsheetPBFController::class, 'rejectDate'])->name('reject-date');

        // Approve/reject by ticket
        Route::post('/{id}/approve', [RptLogsheetPBFController::class, 'approveTicket'])->name('approve');
        Route::post('/{id}/reject', [RptLogsheetPBFController::class, 'rejectTicket'])->name('reject');

        // Exports
        Route::get('/export/view', [RptLogsheetPBFController::class, 'exportLayoutPreview'])->name('export.view');
        Route::get('/export/excel', [RptLogsheetPBFController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [RptLogsheetPBFController::class, 'exportPdf'])->name('export.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Report: F/RFA-003 - Deodorizing & Filtration Section
    |--------------------------------------------------------------------------
    */
    Route::prefix('report-deodorizing')->name('report-deodorizing.')->group(function () {
        Route::get('/', [RptDeodorizingController::class, 'index'])->name('index');
        Route::get('/{id}', [RptDeodorizingController::class, 'show'])->name('show');
        Route::post('/approve-date', [RptDeodorizingController::class, 'approveDate'])->name('approve-date');
        Route::post('/reject-date', [RptDeodorizingController::class, 'rejectDate'])->name('reject-date');
        Route::post('/{id}/approve', [RptDeodorizingController::class, 'approveTicket'])->name('approve');
        Route::post('/{id}/reject', [RptDeodorizingController::class, 'rejectTicket'])->name('reject');
        Route::get('/export/view', [RptDeodorizingController::class, 'exportLayoutPreview'])->name('export.view');
        Route::get('/export/excel', [RptDeodorizingController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [RptDeodorizingController::class, 'exportPdf'])->name('export.pdf');
    });


    /*
    |--------------------------------------------------------------------------
    | Report: F/RFA-004 - Daily Production
    |--------------------------------------------------------------------------
    */


    Route::prefix('report-daily-production')->name('report-daily-production.')->group(function () {

        // General Daily Production (menu utama)
        Route::get('/', [RptDailyProductionController::class, 'index'])->name('index');

        // Refinery
        Route::prefix('refinery')->name('refinery.')->group(function () {
            Route::get('/', [RptDailyPRefController::class, 'index'])->name('index');
            Route::get('/{id}', [RptDailyPRefController::class, 'show'])->name('show');
            Route::post('/approve-date', [RptDailyPRefController::class, 'approveDate'])->name('approve-date');
            Route::post('/reject-date', [RptDailyPRefController::class, 'rejectDate'])->name('reject-date');
            Route::post('/{id}/approve', [RptDailyPRefController::class, 'approveTicket'])->name('approve');
            Route::post('/{id}/reject', [RptDailyPRefController::class, 'rejectTicket'])->name('reject');
            Route::get('/export/view', [RptDailyPRefController::class, 'exportLayoutPreview'])->name('export.view');
            Route::get('/export/excel', [RptDailyPRefController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [RptDailyPRefController::class, 'exportPdf'])->name('export.pdf');
        });

        // Fractination
        Route::prefix('fractionation')->name('fractionation.')->group(function () {
            Route::get('/', [RptDailyPFraController::class, 'index'])->name('index');
            Route::get('/{id}', [RptDailyPFraController::class, 'show'])->name('show');
            Route::post('/approve-date', [RptDailyPFraController::class, 'approveDate'])->name('approve-date');
            Route::post('/reject-date', [RptDailyPFraController::class, 'rejectDate'])->name('reject-date');
            Route::post('/{id}/approve', [RptDailyPFraController::class, 'approveTicket'])->name('approve');
            Route::post('/{id}/reject', [RptDailyPFraController::class, 'rejectTicket'])->name('reject');
            Route::get('/export/view', [RptDailyPFraController::class, 'exportLayoutPreview'])->name('export.view');
            Route::get('/export/excel', [RptDailyPFraController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [RptDailyPFraController::class, 'exportPdf'])->name('export.pdf');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Report: F/RFA-013 - Checklist Lamps and Glass Control
    |--------------------------------------------------------------------------
    */
    Route::resource('report-lampnglass', RptLampGlassController::class)->only(['index', 'show']);
    Route::post('/lamp-glass/{id}/approve', [RptLampGlassController::class, 'approve'])->name('lamp_glass.approve');
    Route::post('/lamp-glass/{id}/reject', [RptLampGlassController::class, 'reject'])->name('lamp_glass.reject');
    Route::get('/report-lampnglass-preview', [RptLampGlassController::class, 'exportLayoutPreview'])->name('report-lampnglass.export.view');
    Route::get('/report-lampnglass-excel', [RptLampGlassController::class, 'exportExcel'])->name('report-lampnglass.export');
    Route::get('/report-lampnglass-pdf', [RptLampGlassController::class, 'exportPdf'])->name('report-lampnglass.export.pdf');


    /*
    |--------------------------------------------------------------------------
    | Logsheet: F/RFA-010 - Monitoring Dry Fractionation Plant Logsheet
    |--------------------------------------------------------------------------
    */
    // Produksi
    // Route::resource('logsheet-dryfractination', LogsheetDryFraController::class)->only(['index', 'show']);
    // Route::post('/logsheet-dryfractination/store', [LogsheetDryFraController::class, 'store'])
    //     ->name('dryfrac.store');


    Route::prefix('report-monitoring-dry-fractionation')->name('report-monitoring-dry-fractionation.')->group(function () {
        Route::get('/', [RptLogsheetDryFraController::class, 'index'])->name('index');
        Route::get('/{id}', [RptLogsheetDryFraController::class, 'show'])->name('show');
        Route::post('/approve-date', [RptLogsheetDryFraController::class, 'approveDate'])->name('approve-date');
        Route::post('/reject-date', [RptLogsheetDryFraController::class, 'rejectDate'])->name('reject-date');
        Route::post('/{id}/approve', [RptLogsheetDryFraController::class, 'approveTicket'])->name('approve');
        Route::post('/{id}/reject', [RptLogsheetDryFraController::class, 'rejectTicket'])->name('reject');
        Route::get('/export/view', [RptLogsheetDryFraController::class, 'exportLayoutPreview'])->name('export.view');
        Route::get('/export/excel', [RptLogsheetDryFraController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [RptLogsheetDryFraController::class, 'exportPdf'])->name('export.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Logsheet: F/RFA-015 - Change Product Checklist
    |--------------------------------------------------------------------------
    */
    Route::prefix('change-product-checklist')->name('change-product-checklist.')->group(function () {
        Route::get('/', [RptChangeProductController::class, 'index'])->name('index');
        Route::get('/{id}', [RptChangeProductController::class, 'show'])->name('show');
        Route::get('/export/preview/{id}', [RptChangeProductController::class, 'exportLayoutPreview'])->name('export.view');
        Route::get('/export/excel', [RptChangeProductController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf/{id}', [RptChangeProductController::class, 'exportPdf'])->name('export.pdf');

        Route::post('/{id}/verify-approve', [RptChangeProductController::class, 'verifyApproval'])->name('verify.approve');
        Route::post('/{id}/verify-reject', [RptChangeProductController::class, 'verifyReject'])->name('verify.reject');

        Route::post('/{id}/check-approve', [RptChangeProductController::class, 'checkApproval'])->name('check.approve');
        Route::post('/{id}/check-reject', [RptChangeProductController::class, 'checkReject'])->name('check.reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Logsheet: F/RFA-016 - Start Up Produksi Checklist
    |--------------------------------------------------------------------------
    */
    Route::prefix('startup-produksi-checklist')->name('startup-produksi-checklist.')->group(function () {
        Route::get('/', [RptStartupProduksiController::class, 'index'])->name('index');
        Route::get('/{id}', [RptStartupProduksiController::class, 'show'])->name('show');
        Route::get('/export/preview/{id}', [RptStartupProduksiController::class, 'exportLayoutPreview'])->name('export.view');
        Route::get('/export/excel', [RptStartupProduksiController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf/{id}', [RptStartupProduksiController::class, 'exportPdf'])->name('export.pdf');

        Route::post('/{id}/verify-approve', [RptStartupProduksiController::class, 'verifyApproval'])->name('verify.approve');
        Route::post('/{id}/verify-reject', [RptStartupProduksiController::class, 'verifyReject'])->name('verify.reject');

        Route::post('/{id}/check-approve', [RptStartupProduksiController::class, 'checkApproval'])->name('check.approve');
        Route::post('/{id}/check-reject', [RptStartupProduksiController::class, 'checkReject'])->name('check.reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Logsheet: F/QCO-001 - Daily Storage Tank Analytical Result
    |--------------------------------------------------------------------------
    */

    Route::prefix('daily-storage-tank-analytical')->name('daily-storage-tank-analytical.')->group(function () {
        Route::get('/', [RptDailyStorageTankAnalyticalController::class, 'index'])->name('index');
        Route::post('/{id}/approve-report', [RptDailyStorageTankAnalyticalController::class, 'approveReport'])->name('approveReport');
        Route::post('/{id}/reject-report', [RptDailyStorageTankAnalyticalController::class, 'rejectReport'])->name('rejectReport');
        Route::get('/{id}', [RptDailyStorageTankAnalyticalController::class, 'show'])->name('show');
        Route::get('/export/view', [RptDailyStorageTankAnalyticalController::class, 'exportLayoutPreview'])->name('export.view');
        Route::get('/export/pdf', [RptDailyStorageTankAnalyticalController::class, 'exportPdf'])->name('export.pdf');
    });
});
