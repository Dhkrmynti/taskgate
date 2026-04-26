<?php

use App\Http\Controllers\DataLogController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\KhsController;
use App\Http\Controllers\ProjectDataController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FinanceRekonController;
use App\Http\Controllers\CommerceRekonController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\RekonController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('login');

    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\MonitoringController::class, 'index'])->name('dashboard');

    // Finance - Admin & Finance Only
    Route::middleware(['role:admin,finance'])->group(function () {
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('/finance/data', [FinanceController::class, 'data'])->name('finance.data');
        Route::get('/finance/pid-suggestions', [FinanceController::class, 'pidSuggestions'])->name('finance.pid-suggestions');
        Route::post('/finance/import', [FinanceController::class, 'import'])->name('finance.import');
        Route::get('/finance/data-log', [DataLogController::class, 'index'])->name('finance.data-log')->defaults('module', 'finance');
        Route::get('/finance/data-log/data', [DataLogController::class, 'data'])->name('finance.data-log.data')->defaults('module', 'finance');
    });

    // Project Data - All Roles (with internal phase logic)
    Route::middleware(['role:admin,finance,warehouse,konstruksi,procurement,commerce'])->group(function () {
        Route::get('/project-data', [ProjectDataController::class, 'index'])->name('project-data.index');
        Route::get('/project-data/data', [ProjectDataController::class, 'data'])->name('project-data.data');
        Route::get('/project-data/{project}/boq-data', [ProjectDataController::class, 'boqData'])->name('project-data.boq-data');

        Route::get('/project-batch', [ProjectDataController::class, 'batchIndex'])->name('project-batch.index');
        Route::get('/project-batch/data', [ProjectDataController::class, 'batchData'])->name('project-batch.data');
        Route::get('/project-batch/{project}', [ProjectDataController::class, 'show'])->name('project-batch.show');


        Route::get('/project-data/{project}', [ProjectDataController::class, 'show'])->name('project-data.show');
        Route::post('/project-data/{project}/evidences', [ProjectDataController::class, 'storeEvidence'])->name('project-data.evidences.store');
    Route::post('/project-data/{project}/subfase-status', [ProjectDataController::class, 'updateSubfaseStatus'])->name('project-data.subfase-status.update');
        Route::get('/project-data/{project}/evidence-files/{evidenceFile}/download', [ProjectDataController::class, 'downloadEvidenceFile'])->name('project-data.evidence-files.download');
        Route::get('/project-data/{project}/evidences/{evidence}/download', [ProjectDataController::class, 'downloadEvidence'])->name('project-data.evidences.download');
        Route::post('/project-data/{project}/procurement1/submit', [ProjectDataController::class, 'submitProcurement1'])->name('project-data.procurement1.submit');
        Route::post('/project-data/{project}/konstruksi/submit', [ProjectDataController::class, 'submitKonstruksi'])->name('project-data.konstruksi.submit');
        Route::post('/project-data/{project}/commerce/submit', [ProjectDataController::class, 'submitCommerce'])->name('project-data.commerce.submit');
        Route::post('/project-data/{project}/warehouse/submit', [ProjectDataController::class, 'submitWarehouse'])->name('project-data.warehouse.submit');
        Route::post('/project-data/{project}/procurement2/submit', [ProjectDataController::class, 'submitProcurement2'])->name('project-data.procurement2.submit');
        Route::post('/project-data/{project}/finance/submit', [ProjectDataController::class, 'submitFinance'])->name('project-data.finance.submit');
    });

    Route::get('/project-data/pid/{pid}/financials', [ProjectDataController::class, 'getFinancialsByPid'])->name('project-data.pid.financials');
    // Notifications
    Route::post('/notifications/{id}/read', [ProjectDataController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [ProjectDataController::class, 'markAllNotificationsAsRead'])->name('notifications.read-all');

    // Admin Only Actions (Project Create, Export, KHS, etc)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/khs', [KhsController::class, 'index'])->name('khs');
        Route::post('/khs', [KhsController::class, 'store'])->name('khs.store');
        Route::get('/khs/data', [KhsController::class, 'data'])->name('khs.data');
        Route::get('/khs/template/download', [KhsController::class, 'downloadTemplate'])->name('khs.template.download');
        Route::post('/khs/import', [KhsController::class, 'import'])->name('khs.import');
        Route::put('/khs/{record}', [KhsController::class, 'update'])->name('khs.update');
        Route::delete('/khs/{record}', [KhsController::class, 'destroy'])->name('khs.destroy');
        
        Route::get('/project-data/create', [ProjectDataController::class, 'create'])->name('project-data.create');
        Route::post('/project-data', [ProjectDataController::class, 'store'])->name('project-data.store');
        Route::get('/project-data/export', [ProjectDataController::class, 'export'])->name('project-data.export');
        
        Route::post('/project-data/{project}/boq-items', [ProjectDataController::class, 'storeBoqItem'])->name('project-data.boq-items.store');
        Route::put('/project-data/{project}/boq-items/{boqItem}', [ProjectDataController::class, 'updateBoqItem'])->name('project-data.boq-items.update');
        Route::delete('/project-data/{project}/boq-items/{boqItem}', [ProjectDataController::class, 'destroyBoqItem'])->name('project-data.boq-items.destroy');
        
        Route::get('/commerce', [ProjectDataController::class, 'create'])->name('commerce');
        Route::get('/commerce/data-log', [DataLogController::class, 'index'])->name('commerce.data-log')->defaults('module', 'commerce');
        Route::get('/commerce/data-log/data', [DataLogController::class, 'data'])->name('commerce.data-log.data')->defaults('module', 'commerce');

        Route::get('/procurement/data-log', [DataLogController::class, 'index'])->name('procurement.data-log')->defaults('module', 'procurement');
        Route::get('/procurement/data-log/data', [DataLogController::class, 'data'])->name('procurement.data-log.data')->defaults('module', 'procurement');
        
        Route::post('/commerce/verify-boq', [ProjectDataController::class, 'verifyBoq'])->name('commerce.boq-verify');
        Route::get('/commerce/template/planning', [ProjectDataController::class, 'downloadPlanningTemplate'])->name('commerce.template-planning');
        
        Route::get('/master-data/{resource}', [MasterDataController::class, 'index'])->name('master-data.index');
        Route::post('/master-data/{resource}', [MasterDataController::class, 'store'])->name('master-data.store');
        Route::put('/master-data/{resource}/{id}', [MasterDataController::class, 'update'])->name('master-data.update');
        Route::delete('/master-data/{resource}/{id}', [MasterDataController::class, 'destroy'])->name('master-data.destroy');
    });
    
    // Unified Role Tasks
    Route::get('/tasks/{role}', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{role}/data', [TaskController::class, 'data'])->name('tasks.data');
    Route::get('/tasks/{role}/manage/{project}', [TaskController::class, 'manage'])->name('tasks.manage');
    Route::post('/tasks/procurement/batch', [TaskController::class, 'processProcurementBatch'])->name('tasks.procurement.batch');
    Route::post('/tasks/commerce/batch', [TaskController::class, 'processCommerceRekonBatch'])->name('tasks.commerce.batch');
    Route::get('/tasks-api/suggestions', [TaskController::class, 'tgidSuggestions'])->name('tasks.suggestions');
    Route::get('/tasks-api/get-project-detail/{id}', [TaskController::class, 'getProjectDetail'])->name('tasks.get-project-detail');

    // Commerce Rekon Dashboard (TGIDRC)
    Route::middleware(['role:admin,commerce'])->group(function () {
        Route::prefix('commerce-rekon')->name('commerce-rekon.')->group(function () {
            Route::get('/', [CommerceRekonController::class, 'index'])->name('index');
            Route::get('/list', [CommerceRekonController::class, 'list'])->name('list');
            Route::get('/autocomplete', [CommerceRekonController::class, 'autocomplete'])->name('autocomplete');
            Route::get('/{commerceRekon}', [CommerceRekonController::class, 'show'])->name('show');
        });
    });

    // Finance Rekon (Batching)
    Route::get('/tasks/finance/rekon-form', [TaskController::class, 'financeRekonForm'])->name('tasks.finance.rekon-form');
    Route::post('/tasks/finance/rekon-process', [TaskController::class, 'processFinanceRekonBatch'])->name('tasks.finance.rekon-process');

    // Finance Rekon Dashboard (TGIDRF)
    Route::middleware(['role:admin,finance'])->group(function () {
        Route::prefix('finance-rekon')->name('finance-rekon.')->group(function () {
            Route::get('/', [FinanceRekonController::class, 'index'])->name('index');
            Route::get('/list', [FinanceRekonController::class, 'list'])->name('list');
            Route::get('/autocomplete', [FinanceRekonController::class, 'autocomplete'])->name('autocomplete');
            Route::get('/{financeRekon}', [FinanceRekonController::class, 'show'])->name('show');
        });
    });

    // Warehouse & Rekon (Admin & Warehouse Only)
    Route::middleware(['role:admin,warehouse'])->group(function () {
        Route::prefix('rekon')->name('rekon.')->group(function () {
            Route::get('/', [RekonController::class, 'index'])->name('index');
            Route::get('/list', [RekonController::class, 'list'])->name('list');
            Route::get('/autocomplete', [RekonController::class, 'autocomplete'])->name('autocomplete');
            Route::get('/{warehouseRekon}', [RekonController::class, 'show'])->name('show');
            Route::get('/{warehouseRekon}/print', [RekonController::class, 'print'])->name('print');
            
            // Downloads
            Route::get('/commerce/{id}/download/{type}', [TaskController::class, 'downloadCommerceRekonFile'])->name('commerce-download');
            Route::get('/warehouse/{id}/download/{type}', [TaskController::class, 'downloadWarehouseRekonFile'])->name('warehouse-download');
            Route::get('/finance/{id}/download/{type}', [TaskController::class, 'downloadFinanceRekonFile'])->name('finance-download');
            Route::get('/batch/{id}/download-boq', [TaskController::class, 'downloadBatchBoq'])->name('batch-download-boq');
        });

        Route::get('/warehouse/import', [WarehouseController::class, 'index'])->name('warehouse.index');
        Route::get('/warehouse/template', [WarehouseController::class, 'downloadTemplate'])->name('warehouse.template');
        Route::get('/warehouse/data', [WarehouseController::class, 'data'])->name('warehouse.data');
        Route::post('/warehouse/import-action', [WarehouseController::class, 'import'])->name('warehouse.import');
        Route::get('/warehouse/data-log', [DataLogController::class, 'index'])->name('warehouse.data-log')->defaults('module', 'warehouse');
        Route::get('/warehouse/data-log/data', [DataLogController::class, 'data'])->name('warehouse.data-log.data')->defaults('module', 'warehouse');

        // New Warehouse Rekon (Batching & Management)
        Route::get('/tasks/warehouse/rekon-form', [TaskController::class, 'warehouseRekonForm'])->name('tasks.warehouse.rekon-form');
        Route::post('/tasks/warehouse/rekon-process', [TaskController::class, 'processWarehouseRekonBatch'])->name('tasks.warehouse.rekon-process');
        Route::post('/tasks/warehouse/update-boq', [TaskController::class, 'updateWarehouseBoq'])->name('tasks.warehouse.update-boq');
    });

});
