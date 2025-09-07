<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModelItemController;
use Controllers\TableProductionControllerTry;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProcessNameController;
use App\Http\Controllers\TableDowntimeController;
use App\Http\Controllers\TableProductionController;
use App\Http\Controllers\DowntimeCategoryController;
use App\Http\Controllers\DowntimeClassificationController;

// Ultra simple health check
Route::get('/up', function () {
    return 'OK';
});

// Health check endpoint for Railway
Route::get('/health', function () {
    try {
        // Test database connection
        DB::connection()->getPdo();

        return response()->json([
            'status' => 'ok',
            'timestamp' => now(),
            'app' => config('app.name', 'StampingPress'),
            'database' => 'connected'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed',
            'timestamp' => now()
        ], 500);
    }
});

// Simple health check without DB
Route::get('/ping', function () {
    return response('pong', 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return "Koneksi ke database berhasil!";
    } catch (\Exception $e) {
        return "Koneksi ke database gagal: " . $e->getMessage();
    }
});

Route::middleware('guest')->group(function () {

    Route::get('/',  [Controllers\HomeController::class, 'index'])->name('home');

    Route::view('/login', 'login');

    Route::post('/login', [Controllers\AuthController::class, 'login'])->name('login');
});

Route::middleware(['auth', 'admin'])->group(function () {

    // Routing for data Users
    Route::resource('users', Controllers\UserController::class);

    Route::get('/master-data/user', [Controllers\UserController::class, 'index'])->name('users');

    Route::post('/master-data/user', [Controllers\AuthController::class, 'register'])->name('users.add');

    Route::get('/users/{user}/edit', [Controllers\UserController::class, 'edit'])->name('users.edit');

    Route::put('/users/{user}', [Controllers\UserController::class, 'update'])->name('users.update');

    Route::delete('users/{user}/delete', [Controllers\UserController::class, 'delete'])->name('del-user');


    // Routing for data Models

    // Route::resource('model-item', Controllers\ModelItemController::class);

    Route::get('/master-data/model-items', [Controllers\ModelItemController::class, 'index'])->name('models');

    Route::post('/master-data/model-items', [Controllers\ModelItemController::class, 'store'])->name('models.add');

    Route::get('/master-data/model-items/all', [Controllers\ModelItemController::class, 'getAll'])->name('models.getAll');

    Route::get('/api/items/{model}', [ModelItemController::class, 'getItemsByModel']);

    Route::get('/master-data/model-items/{model_item}/edit', [Controllers\ModelItemController::class, 'edit'])->name('models.edit');

    Route::put('/master-data/model-items/{model_item}', [Controllers\ModelItemController::class, 'update'])->name('models.update');

    Route::delete('master-data/model-items/{model_item}/delete', [Controllers\ModelItemController::class, 'delete'])->name('models.delete');


    // Routing for data Process Name

    Route::get('/master-data/process-name', [Controllers\ProcessNameController::class, 'index'])->name('process');

    Route::post('/master-data/process-name', [Controllers\ProcessNameController::class, 'store'])->name('process.add');

    Route::get('/master-data/process-name/all', [Controllers\ProcessNameController::class, 'getAll'])->name('process.getAll');

    Route::get('/master-data/process-name/{process_name}/edit', [Controllers\ProcessNameController::class, 'edit'])->name('process.edit');

    Route::put('/master-data/process-name/{process_name}', [Controllers\ProcessNameController::class, 'update'])->name('process.update');

    Route::delete('master-data/process-name/{process_name}/delete', [Controllers\ProcessNameController::class, 'delete'])->name('process.delete');


    // Routing for data Dowtime Category

    Route::get('/master-data/downtime-category', [Controllers\DowntimeCategoryController::class, 'index'])->name('downtime_categories');

    Route::post('/master-data/downtime-category', [Controllers\DowntimeCategoryController::class, 'store'])->name('downtime_categories.add');

    Route::get('/master-data/downtime-category/all', [Controllers\DowntimeCategoryController::class, 'getAll'])->name('downtime_categories.getAll');

    Route::get('/master-data/downtime-category/{downtime_category}/edit', [Controllers\DowntimeCategoryController::class, 'edit'])->name('downtime_categories.edit');

    Route::put('/master-data/downtime-category/{downtime_category}', [Controllers\DowntimeCategoryController::class, 'update'])->name('downtime_categories.update');

    Route::delete('master-data/downtime-category/{downtime_category}/delete', [Controllers\DowntimeCategoryController::class, 'delete'])->name('downtime_categories.delete');


    // Routing for data Dowtime CLassification

    Route::get('/master-data/downtime-classification', [Controllers\DowntimeClassificationController::class, 'index'])->name('dt_classifications');

    Route::post('/master-data/downtime-classification', [Controllers\DowntimeClassificationController::class, 'store'])->name('dt_classifications.add');

    Route::get('/master-data/downtime-classification/all', [Controllers\DowntimeClassificationController::class, 'getAll'])->name('dt_classifications.getAll');

    Route::get('/master-data/downtime-classification/{dt_classification}/edit', [Controllers\DowntimeClassificationController::class, 'edit'])->name('dt_classifications.edit');

    Route::put('/master-data/downtime-classification/{dt_classification}', [Controllers\DowntimeClassificationController::class, 'update'])->name('dt_classifications.update');

    Route::delete('master-data/downtime-classification/{dt_classification}/delete', [Controllers\DowntimeClassificationController::class, 'delete'])->name('dt_classifications.delete');
});


Route::middleware('auth', 'web')->group(function () {

    Route::get('/dashboard', [Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/input-report/production', [Controllers\ProductionController::class, 'index'])->name('form.production');

    Route::post('/input-report/production', [Controllers\ProductionController::class, 'store'])
        ->name('input.production');
    // ->middleware('web');

    Route::get('/master-data/process-name/all', [Controllers\ProcessNameController::class, 'getAll'])->name('process.getAll');

    Route::get('/master-data/downtime-category/all', [Controllers\DowntimeCategoryController::class, 'getAll'])->name('downtime_categories.getAll');

    Route::get('/get-downtime-type/{category_id}', [Controllers\DowntimeCategoryController::class, 'getDowntimeType']);


    // Routing for table production

    Route::get('/table-data/table-production', [Controllers\TableProductionController::class, 'index'])->name('table_production');

    Route::post('/table-data/table-production', [Controllers\TableProductionController::class, 'store'])->name('table_production.add');

    Route::get('/table-data/table-production/all', [Controllers\TableProductionController::class, 'getAll'])->name('table_production.getAll');

    Route::get('/table-data/table-production/{table_production}/edit', [TableProductionController::class, 'edit'])->name('table_production.edit');

    Route::put('/table-data/table-production/{table_production}', [Controllers\TableProductionController::class, 'update'])->name('table_production.update');

    Route::delete('table-data/table-production/{table_production}/delete', [Controllers\TableProductionController::class, 'delete'])->name('table_production.delete');

    Route::get('/table-data/table-production/debug-compare', [TableProductionController::class, 'debugCompare']);

    Route::get('/table-data/table-production/export', [TableProductionController::class, 'export'])->name('table_production.export');


    // Routing for table downtime

    Route::get('/table-data/table-downtime', [Controllers\TableDowntimeController::class, 'index'])->name('table_downtime');

    Route::post('/table-data/table-downtime', [Controllers\TableDowntimeController::class, 'store'])->name('table_downtime.add');

    Route::get('/table-data/table-downtime/all', [Controllers\TableDowntimeController::class, 'getAll'])->name('table_downtime.getAll');

    Route::get('/table-data/table-downtime/{id}/edit', [Controllers\TableDowntimeController::class, 'edit'])->name('table_downtime.edit');

    Route::put('/table-data/table-downtime/{id}', [Controllers\TableDowntimeController::class, 'update'])->name('table_downtime.update');

    Route::delete('table-data/table-downtime/{table_downtime}/delete', [Controllers\TableDowntimeController::class, 'delete'])->name('table_downtime.delete');

    // Routing for table defect

    Route::get('/table-data/table-defect', [Controllers\TableDefectController::class, 'index'])->name('table_defect');

    Route::post('/table-data/table-defect', [Controllers\TableDefectController::class, 'store'])->name('table_defect.add');

    Route::get('/table-data/table-defect/all', [Controllers\TableDefectController::class, 'getAll'])->name('table_defect.getAll');

    Route::get('/table-data/table-defect/{id}/edit', [Controllers\TableDefectController::class, 'edit'])->name('table_defect.edit');

    Route::put('/table-data/table-defect/{id}', [Controllers\TableDefectController::class, 'update'])->name('table_defect.update');

    Route::delete('table-data/table-defect/{id}/delete', [Controllers\TableDefectController::class, 'delete'])->name('table_defect.delete');

    Route::post('/delete-problem-picture/{id}', [Controllers\ProductionController::class, 'deleteProblemPicture'])
        ->name('delete.problem.picture');
});

// Route::post('/logout', [Controllers\AuthController::class, 'logout'])->name('logout');

Route::post('/logout', [Controllers\AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('web', 'auth');
