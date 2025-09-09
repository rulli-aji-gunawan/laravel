<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
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

// Debug environment
Route::get('/debug', function () {
    return response()->json([
        'app_env' => env('APP_ENV'),
        'app_key_set' => env('APP_KEY') ? 'YES' : 'NO',
        'app_key_length' => env('APP_KEY') ? strlen(env('APP_KEY')) : 0,
        'session_driver' => env('SESSION_DRIVER', 'file'),
        'session_lifetime' => env('SESSION_LIFETIME', 120),
        'db_connection' => env('DB_CONNECTION'),
        'db_host' => env('DB_HOST'),
        'db_database' => env('DB_DATABASE'),
        'db_username' => env('DB_USERNAME'),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'storage_writable' => is_writable(storage_path()),
        'storage_path' => storage_path(),
        'session_save_path' => ini_get('session.save_path')
    ]);
});

// Simple database test
Route::get('/test-db-simple', function () {
    try {
        $result = DB::select('SELECT 1 as test');
        return response()->json([
            'status' => 'success',
            'result' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Check database tables
Route::get('/check-tables', function () {
    try {
        $tables = DB::select('SHOW TABLES');

        // Check if users table exists and has data
        $usersExist = DB::select("SHOW TABLES LIKE 'users'");
        $userCount = $usersExist ? DB::table('users')->count() : 0;

        return response()->json([
            'status' => 'success',
            'tables' => $tables,
            'users_table_exists' => !empty($usersExist),
            'users_count' => $userCount
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Test user creation
Route::get('/test-user', function () {
    try {
        // Try to create a test user
        $user = \App\Models\User::firstOrCreate([
            'email' => 'test@stampingpress.com'
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password123')
        ]);

        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Debug API endpoints for model items
Route::get('/debug-api-models', function () {
    try {
        $models = \App\Models\ModelItem::select('model_code')->distinct()->pluck('model_code');
        return response()->json([
            'status' => 'success',
            'models' => $models,
            'count' => $models->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

Route::get('/debug-api-years/{model}', function ($model) {
    try {
        $years = \App\Models\ModelItem::where('model_code', $model)
            ->select('model_year')
            ->distinct()
            ->pluck('model_year');
        return response()->json([
            'status' => 'success',
            'model' => $model,
            'years' => $years,
            'count' => $years->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

Route::get('/debug-api-items/{model}', function ($model) {
    try {
        $items = \App\Models\ModelItem::where('model_code', $model)
            ->select('id', 'model_code', 'item_name', 'product_picture')
            ->get();
        return response()->json([
            'status' => 'success',
            'model' => $model,
            'items' => $items,
            'count' => $items->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Import original database data from backup_production_data.sql
Route::get('/import-original-data', function () {
    try {
        // Read the SQL file
        $sqlFilePath = base_path('backup_production_data.sql');
        
        if (!file_exists($sqlFilePath)) {
            throw new Exception("SQL file not found: " . $sqlFilePath);
        }
        
        $sql = file_get_contents($sqlFilePath);
        
        if (empty($sql)) {
            throw new Exception("SQL file is empty or could not be read");
        }
        
        // Fix encoding issues - convert to UTF-8 if needed
        if (!mb_check_encoding($sql, 'UTF-8')) {
            // Try to detect encoding and convert
            $encoding = mb_detect_encoding($sql, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $sql = mb_convert_encoding($sql, 'UTF-8', $encoding);
            } else {
                // Force UTF-8 encoding, removing invalid characters
                $sql = mb_convert_encoding($sql, 'UTF-8', 'UTF-8');
            }
        }
        
        // Clean any remaining problematic characters
        $sql = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $sql);
        
        // Extract INSERT statements using regex
        preg_match_all('/INSERT\s+INTO\s+[^;]+;/is', $sql, $matches);
        $insertStatements = $matches[0];
        
        // Additional debugging - let's try manual count
        $manualInsertCount = substr_count($sql, 'INSERT INTO');
        $sqlSubstring = substr($sql, 0, 1000); // First 1000 chars for debugging
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $dataImported = [];
        $debugInfo = [];
        
        // If no regex matches but manual count shows inserts, try simpler approach
        if (empty($insertStatements) && $manualInsertCount > 0) {
            // Split by lines and find INSERT lines
            $lines = explode("\n", $sql);
            $insertStatements = [];
            $currentStatement = '';
            $inInsert = false;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                if (stripos($line, 'INSERT INTO') === 0) {
                    if ($inInsert && !empty($currentStatement)) {
                        $insertStatements[] = $currentStatement;
                    }
                    $currentStatement = $line;
                    $inInsert = true;
                } elseif ($inInsert) {
                    $currentStatement .= ' ' . $line;
                    if (substr($line, -1) === ';') {
                        $insertStatements[] = $currentStatement;
                        $currentStatement = '';
                        $inInsert = false;
                    }
                }
            }
            
            if ($inInsert && !empty($currentStatement)) {
                $insertStatements[] = $currentStatement;
            }
        }
        
        DB::beginTransaction();
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        foreach ($insertStatements as $index => $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            try {
                // Extract table name for tracking
                if (preg_match('/INSERT\s+INTO\s+`?([a-zA-Z_]+)`?\s+/i', $statement, $tableMatches)) {
                    $tableName = $tableMatches[1];
                    
                    // Convert to INSERT IGNORE to skip duplicates
                    $statement = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $statement);
                    
                    DB::statement($statement);
                    $successCount++;
                    
                    if (!isset($dataImported[$tableName])) {
                        $dataImported[$tableName] = 0;
                    }
                    $dataImported[$tableName]++;
                    
                    $debugInfo[] = [
                        'table' => $tableName,
                        'statement_preview' => substr($statement, 0, 100) . '...'
                    ];
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $errorMsg = $e->getMessage();
                
                // Track errors but continue
                $errors[] = [
                    'index' => $index,
                    'table' => $tableName ?? 'unknown',
                    'statement' => substr($statement, 0, 150) . '...',
                    'error' => $errorMsg
                ];
                
                // Stop if too many real errors (not duplicates)
                if ($errorCount > 10 && strpos($errorMsg, 'Duplicate entry') === false) {
                    throw new Exception("Too many non-duplicate errors: " . $errorMsg);
                }
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        DB::commit();
        
        // Clean data for JSON response to avoid encoding issues
        $cleanErrors = array_map(function($error) {
            return [
                'index' => $error['index'] ?? null,
                'table' => mb_convert_encoding($error['table'] ?? 'unknown', 'UTF-8', 'UTF-8'),
                'statement' => mb_convert_encoding(substr($error['statement'] ?? '', 0, 100), 'UTF-8', 'UTF-8'),
                'error' => mb_convert_encoding($error['error'] ?? '', 'UTF-8', 'UTF-8')
            ];
        }, array_slice($errors, 0, 5));
        
        $cleanDebugInfo = array_map(function($debug) {
            return [
                'table' => mb_convert_encoding($debug['table'] ?? 'unknown', 'UTF-8', 'UTF-8'),
                'statement_preview' => mb_convert_encoding($debug['statement_preview'] ?? '', 'UTF-8', 'UTF-8')
            ];
        }, array_slice($debugInfo, 0, 3));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Original data imported successfully',
            'successful_statements' => $successCount,
            'failed_statements' => $errorCount,
            'data_imported' => $dataImported,
            'errors' => $cleanErrors,
            'total_insert_statements_found' => count($insertStatements),
            'debug_sample' => $cleanDebugInfo,
            'debug_info' => [
                'manual_insert_count' => $manualInsertCount ?? 0,
                'sql_substring' => mb_convert_encoding(substr($sqlSubstring ?? '', 0, 200), 'UTF-8', 'UTF-8'),
                'file_size' => strlen($sql),
                'has_insert_text' => strpos($sql, 'INSERT INTO') !== false ? 'yes' : 'no'
            ]
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to import original data: ' . mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ], 500);
    }
});

// Alternative: Import from simpler local_data_export.sql
Route::get('/import-simple-data', function () {
    try {
        // Read the simpler SQL file
        $sqlFilePath = base_path('local_data_export.sql');
        
        if (!file_exists($sqlFilePath)) {
            throw new Exception("SQL file not found: " . $sqlFilePath);
        }
        
        $sql = file_get_contents($sqlFilePath);
        
        if (empty($sql)) {
            throw new Exception("SQL file is empty or could not be read");
        }
        
        // Clean SQL
        $sql = preg_replace('/\/\*.*?\*\!/s', '', $sql); // Remove MySQL comments
        $sql = preg_replace('/^--.*$/m', '', $sql); // Remove -- comments
        
        // Split by INSERT statements
        preg_match_all('/INSERT INTO[^;]+;/is', $sql, $matches);
        $insertStatements = $matches[0];
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $results = [];
        
        DB::beginTransaction();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        foreach ($insertStatements as $statement) {
            try {
                // Convert to INSERT IGNORE to skip duplicates
                $statement = preg_replace('/^INSERT INTO/i', 'INSERT IGNORE INTO', trim($statement));
                
                if (preg_match('/INSERT IGNORE INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                    $tableName = $matches[1];
                    
                    DB::statement($statement);
                    $successCount++;
                    
                    if (!isset($results[$tableName])) {
                        $results[$tableName] = 0;
                    }
                    $results[$tableName]++;
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'table' => $tableName ?? 'unknown',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::commit();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Simple data imported successfully',
            'imported_tables' => $results,
            'successful_inserts' => $successCount,
            'failed_inserts' => $errorCount,
            'errors' => $errors
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to import simple data: ' . $e->getMessage()
        ], 500);
    }
});

// Manual migration trigger
Route::get('/run-migration', function () {
    try {
        // Run migration
        Artisan::call('migrate', ['--force' => true]);
        $migrationOutput =  Artisan::output();

        // Run seeder
        Artisan::call('db:seed', ['--class' => 'AdminUserSeeder', '--force' => true]);
        $seederOutput = Artisan::output();

        // Check result
        $userCount = DB::table('users')->count();

        return response()->json([
            'status' => 'success',
            'migration_output' => $migrationOutput,
            'seeder_output' => $seederOutput,
            'users_created' => $userCount
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Create admin user manually
Route::get('/create-admin', function () {
    try {
        // Check if admin already exists
        $existingAdmin = \App\Models\User::where('email', 'admin@email.com')->first();

        if ($existingAdmin) {
            return response()->json([
                'status' => 'info',
                'message' => 'Admin user already exists',
                'admin_email' => 'admin@email.com'
            ]);
        }

        // Create admin user
        $admin = \App\Models\User::create([
            'name' => 'Administrator',
            'email' => 'admin@email.com',
            'password' => Hash::make('aaaaa'),
            'email_verified_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin user created successfully',
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'login_credentials' => [
                'email' => 'admin@email.com',
                'password' => 'aaaaa'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
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

// Test login process debug
Route::get('/test-login-debug', function () {
    try {
        // Test user exists
        $user = \App\Models\User::where('email', 'admin@email.com')->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }

        // Test password verification
        $passwordCheck = Hash::check('aaaaa', $user->password);

        // Test auth attempt
        $credentials = ['email' => 'admin@email.com', 'password' => 'aaaaa'];
        $authAttempt = Auth::attempt($credentials);

        // Check session configuration
        $sessionConfig = [
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime'),
            'path' => config('session.path'),
            'domain' => config('session.domain'),
            'secure' => config('session.secure'),
            'same_site' => config('session.same_site')
        ];

        return response()->json([
            'status' => 'debug_info',
            'user_found' => !!$user,
            'user_id' => $user->id,
            'password_correct' => $passwordCheck,
            'auth_attempt' => $authAttempt,
            'session_config' => $sessionConfig,
            'current_auth_user' => Auth::id()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
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

    Route::get('/api/years/{model}', [ModelItemController::class, 'getYearsByModel']);

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

// Debug dashboard data
Route::get('/debug-dashboard', function () {
    try {
        // Check if production tables have data
        $tableProductionCount = DB::table('table_productions')->count();
        $tableDowntimeCount = DB::table('table_downtimes')->count();
        $tableDefectCount = DB::table('table_defects')->count();

        // Check models
        $modelItemsCount = DB::table('model_items')->count();
        $downtimeCategoriesCount = DB::table('downtime_categories')->count();

        return response()->json([
            'status' => 'success',
            'table_counts' => [
                'table_productions' => $tableProductionCount,
                'table_downtimes' => $tableDowntimeCount,
                'table_defects' => $tableDefectCount,
                'model_items' => $modelItemsCount,
                'downtime_categories' => $downtimeCategoriesCount
            ],
            'message' => 'Dashboard needs production data to work properly'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Import sample data from manual_setup.sql
Route::get('/import-sample-data', function () {
    try {
        DB::beginTransaction();
        
        // Insert Admin User
        DB::statement("INSERT IGNORE INTO users (id, name, email, password, is_admin, created_at, updated_at) VALUES 
            (1, 'Admin User', 'admin@email.com', '\$2y\$12\$OiP2UF66w5DyZ2aomWPU3.bjeskEnr5HSs9dahYbVTXfCX/njCHae', 1, NOW(), NOW())");

        // Insert Model Items
        DB::statement("INSERT IGNORE INTO model_items (id, model_code, model_year, item_name, created_at, updated_at) VALUES 
            (1, 'FFVV', '2026', 'ITEM PERTAMA', NOW(), NOW()),
            (2, 'FFVV', '2026', 'ITEM KEDUA', NOW(), NOW()),
            (3, 'FFVV', '2026', 'ITEM KETIGA', NOW(), NOW())");

        // Insert Downtime Classifications
        DB::statement("INSERT IGNORE INTO downtime_classifications (id, downtime_classification, created_at, updated_at) VALUES 
            (1, 'Planned Downtime', NOW(), NOW()),
            (2, 'Gomi', NOW(), NOW()),
            (3, 'Kiriko', NOW(), NOW()),
            (5, 'Dent', NOW(), NOW()),
            (6, 'Scratch', NOW(), NOW()),
            (7, 'Crack', NOW(), NOW()),
            (8, 'Necking', NOW(), NOW()),
            (9, 'Burry', NOW(), NOW()),
            (10, 'Ding', NOW(), NOW())");

        // Insert Process Names
        DB::statement("INSERT IGNORE INTO process_names (id, process_name, created_at, updated_at) VALUES 
            (1, 'Line All', NOW(), NOW()),
            (16, 'OP.10', NOW(), NOW()),
            (18, 'OP.20', NOW(), NOW()),
            (20, 'OP.30', NOW(), NOW()),
            (22, 'OP.40', NOW(), NOW())");

        DB::commit();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Sample data imported successfully',
            'imported' => [
                'users' => 1,
                'model_items' => 3,
                'downtime_classifications' => 9,
                'process_names' => 5
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Simple admin user creation
Route::get('/create-admin', function () {
    try {
        // Create simple admin user
        $adminExists = DB::table('users')->where('email', 'admin@email.com')->exists();
        
        if (!$adminExists) {
            DB::table('users')->insert([
                'name' => 'Admin User',
                'email' => 'admin@email.com',
                'password' => '$2y$12$OiP2UF66w5DyZ2aomWPU3.bjeskEnr5HSs9dahYbVTXfCX/njCHae', // password: aaaaa
                'is_admin' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $message = 'Admin user created successfully';
        } else {
            DB::table('users')->where('email', 'admin@email.com')->update(['is_admin' => 1]);
            $message = 'Admin user already exists, updated admin status';
        }
        
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'login_info' => [
                'email' => 'admin@email.com',
                'password' => 'aaaaa'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Test route
Route::get('/test-import', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Test route is working'
    ]);
});

// Fix table structure to match manual_setup.sql
Route::get('/fix-table-structure', function () {
    try {
        // Add missing columns to model_items table
        $results = [];
        
        // Check if model_code column exists
        try {
            $columns = DB::select("SHOW COLUMNS FROM model_items LIKE 'model_code'");
            if (empty($columns)) {
                DB::statement("ALTER TABLE model_items ADD COLUMN model_code VARCHAR(255) AFTER id");
                $results[] = "Added model_code column";
            } else {
                $results[] = "model_code column already exists";
            }
        } catch (\Exception $e) {
            $results[] = "Error adding model_code: " . $e->getMessage();
        }
        
        // Check if model_year column exists  
        try {
            $columns = DB::select("SHOW COLUMNS FROM model_items LIKE 'model_year'");
            if (empty($columns)) {
                DB::statement("ALTER TABLE model_items ADD COLUMN model_year VARCHAR(255) AFTER model_code");
                $results[] = "Added model_year column";
            } else {
                $results[] = "model_year column already exists";
            }
        } catch (\Exception $e) {
            $results[] = "Error adding model_year: " . $e->getMessage();
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Table structure update completed',
            'changes' => $results
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Fix column names to match manual_setup.sql
Route::get('/fix-column-names', function () {
    try {
        $results = [];
        
        // Check and rename dt_classification to downtime_classification
        $columns = DB::select("SHOW COLUMNS FROM downtime_classifications LIKE 'dt_classification'");
        if (!empty($columns)) {
            DB::statement("ALTER TABLE downtime_classifications CHANGE dt_classification downtime_classification VARCHAR(255)");
            $results[] = "Renamed dt_classification to downtime_classification";
        } else {
            $results[] = "dt_classification column not found";
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Column names fixed successfully',
            'changes' => $results
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Drop model_name column to match manual_setup.sql structure
Route::get('/drop-model-name', function () {
    try {
        // Check if model_name column exists first
        $columns = DB::select("SHOW COLUMNS FROM model_items LIKE 'model_name'");
        if (!empty($columns)) {
            DB::statement("ALTER TABLE model_items DROP COLUMN model_name");
            return response()->json([
                'status' => 'success',
                'message' => 'model_name column dropped successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'info',
                'message' => 'model_name column does not exist'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Add model_code column only
Route::get('/add-model-code', function () {
    try {
        DB::statement("ALTER TABLE model_items ADD COLUMN model_code VARCHAR(255) AFTER id");
        return response()->json([
            'status' => 'success',
            'message' => 'model_code column added successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Add model_year column only
Route::get('/add-model-year', function () {
    try {
        DB::statement("ALTER TABLE model_items ADD COLUMN model_year VARCHAR(255) AFTER model_code");
        return response()->json([
            'status' => 'success',
            'message' => 'model_year column added successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Check table structures
Route::get('/check-tables', function () {
    try {
        $tables = [];
        
        // Check model_items table structure
        $modelItemsColumns = DB::select("DESCRIBE model_items");
        $tables['model_items'] = $modelItemsColumns;
        
        // Check process_names table structure  
        $processNamesColumns = DB::select("DESCRIBE process_names");
        $tables['process_names'] = $processNamesColumns;
        
        // Check downtime_classifications table structure
        $downtimeClassColumns = DB::select("DESCRIBE downtime_classifications");
        $tables['downtime_classifications'] = $downtimeClassColumns;
        
        return response()->json([
            'status' => 'success',
            'tables' => $tables
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Import data with correct structure (model_code, model_year)
Route::get('/import-correct-data', function () {
    try {
        DB::beginTransaction();
        
        $results = [];
        
        // Insert Model Items with all required columns
        $modelsInserted = 0;
        $models = [
            ['model_code' => 'FFVV', 'model_year' => '2026', 'item_name' => 'ITEM PERTAMA'],
            ['model_code' => 'FFVV', 'model_year' => '2026', 'item_name' => 'ITEM KEDUA'],
            ['model_code' => 'FFVV', 'model_year' => '2026', 'item_name' => 'ITEM KETIGA']
        ];
        
        foreach ($models as $model) {
            $exists = DB::table('model_items')
                ->where('model_code', $model['model_code'])
                ->where('item_name', $model['item_name'])
                ->exists();
                
            if (!$exists) {
                DB::table('model_items')->insert([
                    'model_code' => $model['model_code'],
                    'model_year' => $model['model_year'],
                    'item_name' => $model['item_name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $modelsInserted++;
            }
        }
        $results['model_items'] = $modelsInserted;
        
        // Insert Process Names
        $processesInserted = 0;
        $processes = ['Line All', 'OP.10', 'OP.20', 'OP.30', 'OP.40'];
        
        foreach ($processes as $process) {
            $exists = DB::table('process_names')
                ->where('process_name', $process)
                ->exists();
                
            if (!$exists) {
                DB::table('process_names')->insert([
                    'process_name' => $process,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $processesInserted++;
            }
        }
        $results['process_names'] = $processesInserted;
        
        // Insert Downtime Classifications
        $classificationsInserted = 0;
        $classifications = [
            'Planned Downtime', 'Gomi', 'Kiriko', 'Dent', 'Scratch', 'Crack', 'Necking', 'Burry', 'Ding'
        ];
        
        foreach ($classifications as $classification) {
            $exists = DB::table('downtime_classifications')
                ->where('downtime_classification', $classification)
                ->exists();
                
            if (!$exists) {
                DB::table('downtime_classifications')->insert([
                    'downtime_classification' => $classification,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $classificationsInserted++;
            }
        }
        $results['downtime_classifications'] = $classificationsInserted;
        
        DB::commit();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data imported successfully with correct structure',
            'inserted' => $results
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Simple data import route
Route::get('/setup-data', function () {
    try {
        DB::beginTransaction();
        
        // Insert Model Items (sesuai struktur Railway: model_name, item_name)
        $modelsInserted = 0;
        $models = [
            ['model_name' => 'FFVV', 'item_name' => 'ITEM PERTAMA'],
            ['model_name' => 'FFVV', 'item_name' => 'ITEM KEDUA'],
            ['model_name' => 'FFVV', 'item_name' => 'ITEM KETIGA']
        ];
        
        foreach ($models as $model) {
            $exists = DB::table('model_items')
                ->where('model_name', $model['model_name'])
                ->where('item_name', $model['item_name'])
                ->exists();
                
            if (!$exists) {
                DB::table('model_items')->insert([
                    'model_name' => $model['model_name'],
                    'item_name' => $model['item_name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $modelsInserted++;
            }
        }
        
        // Insert Process Names
        $processesInserted = 0;
        $processes = [
            'Line All', 'OP.10', 'OP.20', 'OP.30', 'OP.40'
        ];
        
        foreach ($processes as $process) {
            $exists = DB::table('process_names')
                ->where('process_name', $process)
                ->exists();
                
            if (!$exists) {
                DB::table('process_names')->insert([
                    'process_name' => $process,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $processesInserted++;
            }
        }
        
        // Insert Downtime Classifications
        $classificationsInserted = 0;
        $classifications = [
            'Planned Downtime', 'Gomi', 'Kiriko', 'Dent', 'Scratch', 'Crack', 'Necking', 'Burry', 'Ding'
        ];
        
        foreach ($classifications as $classification) {
            $exists = DB::table('downtime_classifications')
                ->where('downtime_classification', $classification)
                ->exists();
                
            if (!$exists) {
                DB::table('downtime_classifications')->insert([
                    'downtime_classification' => $classification,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $classificationsInserted++;
            }
        }
        
        DB::commit();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data setup completed successfully',
            'inserted' => [
                'model_items' => $modelsInserted,
                'process_names' => $processesInserted,
                'downtime_classifications' => $classificationsInserted
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Check migration status
Route::get('/check-migrations', function () {
    try {
        // Get all migrations that have been run
        $migrationsRun = DB::table('migrations')->pluck('migration')->toArray();

        // Get all migration files - simplified approach
        $migrationFiles = [
            '0001_01_01_000000_create_users_table',
            '2025_05_04_213826_create_table_productions_table',
            '2025_06_30_061935_create_table_downtimes_table',
            '2025_07_15_055100_create_table_defects_table'
        ];

        // Find missing migrations
        $missingMigrations = array_diff($migrationFiles, $migrationsRun);

        // Check specific tables
        $tableChecks = [];
        $tables = ['table_productions', 'table_downtimes', 'table_defects'];
        foreach ($tables as $table) {
            try {
                DB::select("SHOW TABLES LIKE '{$table}'");
                $tableChecks[$table] = 'exists';
            } catch (\Exception $e) {
                $tableChecks[$table] = 'missing';
            }
        }

        return response()->json([
            'status' => 'success',
            'total_migrations_run' => count($migrationsRun),
            'missing_migrations' => array_values($missingMigrations),
            'table_status' => $tableChecks,
            'last_5_migrations_run' => array_slice($migrationsRun, -5)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Force migrate specific production tables
Route::get('/migrate-production-tables', function () {
    try {
        // Use Artisan directly instead of shell_exec
        $output = [];

        // Check current table status first
        $beforeStatus = [];
        $tables = ['table_productions', 'table_downtimes', 'table_defects'];
        foreach ($tables as $table) {
            try {
                $exists = DB::select("SHOW TABLES LIKE '{$table}'");
                $beforeStatus[$table] = !empty($exists) ? 'exists' : 'missing';
            } catch (\Exception $e) {
                $beforeStatus[$table] = 'error';
            }
        }

        // Run migration
        Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = Artisan::output();

        // Check tables after migration
        $afterStatus = [];
        foreach ($tables as $table) {
            try {
                $exists = DB::select("SHOW TABLES LIKE '{$table}'");
                $afterStatus[$table] = !empty($exists) ? 'exists' : 'missing';
            } catch (\Exception $e) {
                $afterStatus[$table] = 'error: ' . $e->getMessage();
            }
        }

        return response()->json([
            'status' => 'success',
            'before_migration' => $beforeStatus,
            'migration_output' => $migrationOutput,
            'after_migration' => $afterStatus
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Fix migration conflicts - mark problematic migrations as run
Route::get('/fix-migration-conflicts', function () {
    try {
        // List of migrations that have conflicts but tables exist
        $conflictMigrations = [
            '2025_05_04_213826_create_table_productions_table',
            '2025_06_30_061935_create_table_downtimes_table',
            '2025_07_15_055100_create_table_defects_table'
        ];

        $results = [];

        foreach ($conflictMigrations as $migration) {
            // Check if migration already recorded
            $exists = DB::table('migrations')->where('migration', $migration)->exists();

            if (!$exists) {
                // Insert migration record without running it
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => DB::table('migrations')->max('batch') + 1
                ]);
                $results[$migration] = 'marked_as_run';
            } else {
                $results[$migration] = 'already_recorded';
            }
        }

        // Now try a safe migration
        Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = Artisan::output();

        return response()->json([
            'status' => 'success',
            'conflict_fixes' => $results,
            'migration_output' => $migrationOutput,
            'message' => 'Migration conflicts resolved'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Manual database structure fix
Route::get('/manual-db-fix', function () {
    try {
        $results = [];

        // Drop and recreate table_productions with correct structure
        DB::statement('DROP TABLE IF EXISTS table_productions');
        $results['table_productions'] = 'dropped';

        // Create table_productions with correct structure
        DB::statement('
            CREATE TABLE table_productions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fy_n VARCHAR(255),
                date DATE,
                shift VARCHAR(255),
                line VARCHAR(255),
                `group` VARCHAR(255),
                model VARCHAR(255),
                item_name VARCHAR(255),
                target INT,
                actual INT,
                achievement DECIMAL(5,2),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ');
        $results['table_productions'] = 'created';

        // Drop and recreate table_downtimes with correct structure
        DB::statement('DROP TABLE IF EXISTS table_downtimes');

        DB::statement('
            CREATE TABLE table_downtimes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fy_n VARCHAR(255),
                date DATE,
                shift VARCHAR(255),
                line VARCHAR(255),
                `group` VARCHAR(255),
                model VARCHAR(255),
                item_name VARCHAR(255),
                downtime_type VARCHAR(255),
                dt_category VARCHAR(255),
                dt_classification VARCHAR(255),
                total_time INT,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ');
        $results['table_downtimes'] = 'created';

        // Drop and recreate table_defects with correct structure
        DB::statement('DROP TABLE IF EXISTS table_defects');

        DB::statement('
            CREATE TABLE table_defects (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fy_n VARCHAR(255),
                date DATE,
                shift VARCHAR(255),
                line VARCHAR(255),
                `group` VARCHAR(255),
                model VARCHAR(255),
                item_name VARCHAR(255),
                defect_category VARCHAR(255),
                total_defect INT,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )
        ');
        $results['table_defects'] = 'created';

        // Mark migrations as completed
        $migrations = [
            '2025_05_04_213826_create_table_productions_table',
            '2025_06_30_061935_create_table_downtimes_table',
            '2025_07_15_055100_create_table_defects_table'
        ];

        foreach ($migrations as $migration) {
            DB::table('migrations')->updateOrInsert(
                ['migration' => $migration],
                ['batch' => DB::table('migrations')->max('batch') + 1]
            );
        }
        $results['migrations_marked'] = 'completed';

        // Test tables
        $tableTests = [];
        $tableTests['table_productions'] = DB::table('table_productions')->count();
        $tableTests['table_downtimes'] = DB::table('table_downtimes')->count();
        $tableTests['table_defects'] = DB::table('table_defects')->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Database structure manually fixed',
            'operations' => $results,
            'table_counts' => $tableTests
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Complete database reset and rebuild
Route::get('/reset-database', function () {
    try {
        $results = [];

        // Get all tables first
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            if ($tableName !== 'migrations') { // Keep migrations table
                $tableNames[] = $tableName;
            }
        }

        // Drop all tables except migrations
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tableNames as $tableName) {
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            $results['dropped'][] = $tableName;
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Clear migrations table (except for essential ones)
        DB::table('migrations')->truncate();
        $results['migrations_cleared'] = true;

        // Run fresh migration
        Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = Artisan::output();
        $results['migration_output'] = $migrationOutput;

        // Run seeder for admin user
        Artisan::call('db:seed', ['--class' => 'AdminUserSeeder', '--force' => true]);
        $seederOutput = Artisan::output();
        $results['seeder_output'] = $seederOutput;

        // Check final state
        $finalTables = DB::select('SHOW TABLES');
        $finalTableNames = [];
        foreach ($finalTables as $table) {
            $finalTableNames[] = array_values((array) $table)[0];
        }

        $results['final_tables'] = $finalTableNames;
        $results['user_count'] = DB::table('users')->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Database completely reset and rebuilt',
            'results' => $results
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Create proper table structure based on Models
Route::get('/create-proper-tables', function () {
    try {
        $results = [];

        // Drop existing tables first
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement("DROP TABLE IF EXISTS `table_defects`");
        DB::statement("DROP TABLE IF EXISTS `table_downtimes`");
        DB::statement("DROP TABLE IF EXISTS `table_productions`");
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Create table_productions based on TableProduction model
        DB::statement("
            CREATE TABLE `table_productions` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `reporter` varchar(255) DEFAULT NULL,
                `group` varchar(255) DEFAULT NULL,
                `date` date DEFAULT NULL,
                `fy_n` varchar(255) DEFAULT NULL,
                `shift` varchar(255) DEFAULT NULL,
                `line` varchar(255) DEFAULT NULL,
                `start_time` time DEFAULT NULL,
                `finish_time` time DEFAULT NULL,
                `total_prod_time` varchar(255) DEFAULT NULL,
                `model` varchar(255) DEFAULT NULL,
                `model_year` varchar(255) DEFAULT NULL,
                `spm` varchar(255) DEFAULT NULL,
                `item_name` varchar(255) DEFAULT NULL,
                `coil_no` varchar(255) DEFAULT NULL,
                `plan_a` int DEFAULT NULL,
                `plan_b` int DEFAULT NULL,
                `ok_a` int DEFAULT NULL,
                `ok_b` int DEFAULT NULL,
                `rework_a` int DEFAULT NULL,
                `rework_b` int DEFAULT NULL,
                `scrap_a` int DEFAULT NULL,
                `scrap_b` int DEFAULT NULL,
                `sample_a` int DEFAULT NULL,
                `sample_b` int DEFAULT NULL,
                `rework_exp` text,
                `scrap_exp` text,
                `trial_sample_exp` text,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create table_downtimes based on TableDowntime model
        DB::statement("
            CREATE TABLE `table_downtimes` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `table_production_id` bigint unsigned DEFAULT NULL,
                `reporter` varchar(255) DEFAULT NULL,
                `group` varchar(255) DEFAULT NULL,
                `date` date DEFAULT NULL,
                `fy_n` varchar(255) DEFAULT NULL,
                `shift` varchar(255) DEFAULT NULL,
                `line` varchar(255) DEFAULT NULL,
                `model` varchar(255) DEFAULT NULL,
                `model_year` varchar(255) DEFAULT NULL,
                `item_name` varchar(255) DEFAULT NULL,
                `coil_no` varchar(255) DEFAULT NULL,
                `time_from` time DEFAULT NULL,
                `time_until` time DEFAULT NULL,
                `total_time` varchar(255) DEFAULT NULL,
                `process_name` varchar(255) DEFAULT NULL,
                `dt_category` varchar(255) DEFAULT NULL,
                `downtime_type` varchar(255) DEFAULT NULL,
                `dt_classification` varchar(255) DEFAULT NULL,
                `problem_description` text,
                `root_cause` text,
                `counter_measure` text,
                `pic` varchar(255) DEFAULT NULL,
                `status` varchar(255) DEFAULT NULL,
                `problem_picture` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `table_downtimes_table_production_id_foreign` (`table_production_id`),
                CONSTRAINT `table_downtimes_table_production_id_foreign` FOREIGN KEY (`table_production_id`) REFERENCES `table_productions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create table_defects based on TableDefect model
        DB::statement("
            CREATE TABLE `table_defects` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `table_production_id` bigint unsigned DEFAULT NULL,
                `reporter` varchar(255) DEFAULT NULL,
                `group` varchar(255) DEFAULT NULL,
                `date` date DEFAULT NULL,
                `fy_n` varchar(255) DEFAULT NULL,
                `shift` varchar(255) DEFAULT NULL,
                `line` varchar(255) DEFAULT NULL,
                `model` varchar(255) DEFAULT NULL,
                `model_year` varchar(255) DEFAULT NULL,
                `item_name` varchar(255) DEFAULT NULL,
                `coil_no` varchar(255) DEFAULT NULL,
                `defect_category` varchar(255) DEFAULT NULL,
                `defect_name` varchar(255) DEFAULT NULL,
                `defect_qty_a` int DEFAULT NULL,
                `defect_qty_b` int DEFAULT NULL,
                `defect_area` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `table_defects_table_production_id_foreign` (`table_production_id`),
                CONSTRAINT `table_defects_table_production_id_foreign` FOREIGN KEY (`table_production_id`) REFERENCES `table_productions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Mark migrations as completed to prevent conflicts
        $migrationFiles = [
            '2024_08_03_080503_create_table_productions_table',
            '2024_08_03_080717_create_table_downtimes_table',
            '2024_08_03_080727_create_table_defects_table'
        ];

        foreach ($migrationFiles as $migration) {
            DB::table('migrations')->updateOrInsert(
                ['migration' => $migration],
                ['batch' => 1]
            );
        }

        $results['tables_created'] = ['table_productions', 'table_downtimes', 'table_defects'];
        $results['migrations_marked'] = $migrationFiles;

        // Test table structure
        $tableTests = [];
        $tables = ['table_productions', 'table_downtimes', 'table_defects'];
        foreach ($tables as $table) {
            $columns = DB::select("DESCRIBE {$table}");
            $tableTests[$table] = [
                'exists' => true,
                'column_count' => count($columns),
                'columns' => array_map(function ($col) {
                    return $col->Field;
                }, $columns)
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Production tables created with proper structure',
            'results' => $results,
            'table_structure' => $tableTests
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Generate SQL export structure for local MySQL Workbench
Route::get('/generate-sql-export', function () {
    try {
        $sql = [];

        // SQL to export table_productions structure and data
        $sql[] = "-- Export structure for table_productions";
        $sql[] = "DROP TABLE IF EXISTS `table_productions`;";
        $sql[] = "CREATE TABLE `table_productions` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `reporter` varchar(255) DEFAULT NULL,
            `group` varchar(255) DEFAULT NULL,
            `date` date DEFAULT NULL,
            `fy_n` varchar(255) DEFAULT NULL,
            `shift` varchar(255) DEFAULT NULL,
            `line` varchar(255) DEFAULT NULL,
            `start_time` time DEFAULT NULL,
            `finish_time` time DEFAULT NULL,
            `total_prod_time` varchar(255) DEFAULT NULL,
            `model` varchar(255) DEFAULT NULL,
            `model_year` varchar(255) DEFAULT NULL,
            `spm` varchar(255) DEFAULT NULL,
            `item_name` varchar(255) DEFAULT NULL,
            `coil_no` varchar(255) DEFAULT NULL,
            `plan_a` int DEFAULT NULL,
            `plan_b` int DEFAULT NULL,
            `ok_a` int DEFAULT NULL,
            `ok_b` int DEFAULT NULL,
            `rework_a` int DEFAULT NULL,
            `rework_b` int DEFAULT NULL,
            `scrap_a` int DEFAULT NULL,
            `scrap_b` int DEFAULT NULL,
            `sample_a` int DEFAULT NULL,
            `sample_b` int DEFAULT NULL,
            `rework_exp` text,
            `scrap_exp` text,
            `trial_sample_exp` text,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sql[] = "";
        $sql[] = "-- Export structure for table_downtimes";
        $sql[] = "DROP TABLE IF EXISTS `table_downtimes`;";
        $sql[] = "CREATE TABLE `table_downtimes` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `table_production_id` bigint unsigned DEFAULT NULL,
            `reporter` varchar(255) DEFAULT NULL,
            `group` varchar(255) DEFAULT NULL,
            `date` date DEFAULT NULL,
            `fy_n` varchar(255) DEFAULT NULL,
            `shift` varchar(255) DEFAULT NULL,
            `line` varchar(255) DEFAULT NULL,
            `model` varchar(255) DEFAULT NULL,
            `model_year` varchar(255) DEFAULT NULL,
            `item_name` varchar(255) DEFAULT NULL,
            `coil_no` varchar(255) DEFAULT NULL,
            `time_from` time DEFAULT NULL,
            `time_until` time DEFAULT NULL,
            `total_time` varchar(255) DEFAULT NULL,
            `process_name` varchar(255) DEFAULT NULL,
            `dt_category` varchar(255) DEFAULT NULL,
            `downtime_type` varchar(255) DEFAULT NULL,
            `dt_classification` varchar(255) DEFAULT NULL,
            `problem_description` text,
            `root_cause` text,
            `counter_measure` text,
            `pic` varchar(255) DEFAULT NULL,
            `status` varchar(255) DEFAULT NULL,
            `problem_picture` varchar(255) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `table_downtimes_table_production_id_foreign` (`table_production_id`),
            CONSTRAINT `table_downtimes_table_production_id_foreign` FOREIGN KEY (`table_production_id`) REFERENCES `table_productions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sql[] = "";
        $sql[] = "-- Export structure for table_defects";
        $sql[] = "DROP TABLE IF EXISTS `table_defects`;";
        $sql[] = "CREATE TABLE `table_defects` (
            `id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `table_production_id` bigint unsigned DEFAULT NULL,
            `reporter` varchar(255) DEFAULT NULL,
            `group` varchar(255) DEFAULT NULL,
            `date` date DEFAULT NULL,
            `fy_n` varchar(255) DEFAULT NULL,
            `shift` varchar(255) DEFAULT NULL,
            `line` varchar(255) DEFAULT NULL,
            `model` varchar(255) DEFAULT NULL,
            `model_year` varchar(255) DEFAULT NULL,
            `item_name` varchar(255) DEFAULT NULL,
            `coil_no` varchar(255) DEFAULT NULL,
            `defect_category` varchar(255) DEFAULT NULL,
            `defect_name` varchar(255) DEFAULT NULL,
            `defect_qty_a` int DEFAULT NULL,
            `defect_qty_b` int DEFAULT NULL,
            `defect_area` varchar(255) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `table_defects_table_production_id_foreign` (`table_production_id`),
            CONSTRAINT `table_defects_table_production_id_foreign` FOREIGN KEY (`table_production_id`) REFERENCES `table_productions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $sql[] = "";
        $sql[] = "-- Instructions:";
        $sql[] = "-- 1. Execute the above SQL in your Railway MySQL database";
        $sql[] = "-- 2. Export your local MySQL Workbench data using mysqldump:";
        $sql[] = "-- mysqldump -u root -p your_local_db table_productions table_downtimes table_defects --no-create-info --extended-insert > data_export.sql";
        $sql[] = "-- 3. Import the data into Railway MySQL database";

        $fullSql = implode("\n", $sql);

        return response($fullSql, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="railway_table_structure.sql"'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

// Complete application reset and fix
Route::get('/complete-fix', function () {
    try {
        $results = [];
        
        // 1. Clear all caches first
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $results['caches_cleared'] = true;
        
        // 2. Reset database completely - drop all tables
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            $tableNames[] = $tableName;
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tableNames as $tableName) {
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $results['all_tables_dropped'] = $tableNames;
        
        // 3. Create migrations table manually
        DB::statement("
            CREATE TABLE `migrations` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `migration` varchar(255) NOT NULL,
                `batch` int NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // 4. Run only essential migrations to avoid conflicts
        $essentialMigrations = [
            '0001_01_01_000000_create_users_table',
            '0001_01_01_000001_create_cache_table', 
            '0001_01_01_000002_create_jobs_table'
        ];
        
        foreach ($essentialMigrations as $migration) {
            try {
                Artisan::call('migrate:refresh', [
                    '--path' => 'database/migrations/' . $migration . '.php',
                    '--force' => true
                ]);
            } catch (\Exception $e) {
                // Continue if migration fails
                $results['migration_warnings'][] = $migration . ': ' . $e->getMessage();
            }
        }
        
        // 5. Create admin user manually
        DB::statement("
            INSERT INTO `users` (`name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) 
            VALUES ('Admin User', 'admin@email.com', NULL, ?, NULL, NOW(), NOW())
        ", [Hash::make('aaaaa')]);
        
        $results['admin_user_created'] = true;
        
        // 6. Create production tables manually with correct structure
        DB::statement("
            CREATE TABLE `table_productions` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `reporter` varchar(255) DEFAULT NULL,
                `group` varchar(255) DEFAULT NULL,
                `date` date DEFAULT NULL,
                `fy_n` varchar(255) DEFAULT NULL,
                `shift` varchar(255) DEFAULT NULL,
                `line` varchar(255) DEFAULT NULL,
                `start_time` time DEFAULT NULL,
                `finish_time` time DEFAULT NULL,
                `total_prod_time` varchar(255) DEFAULT NULL,
                `model` varchar(255) DEFAULT NULL,
                `model_year` varchar(255) DEFAULT NULL,
                `spm` varchar(255) DEFAULT NULL,
                `item_name` varchar(255) DEFAULT NULL,
                `coil_no` varchar(255) DEFAULT NULL,
                `plan_a` int DEFAULT NULL,
                `plan_b` int DEFAULT NULL,
                `ok_a` int DEFAULT NULL,
                `ok_b` int DEFAULT NULL,
                `rework_a` int DEFAULT NULL,
                `rework_b` int DEFAULT NULL,
                `scrap_a` int DEFAULT NULL,
                `scrap_b` int DEFAULT NULL,
                `sample_a` int DEFAULT NULL,
                `sample_b` int DEFAULT NULL,
                `rework_exp` text,
                `scrap_exp` text,
                `trial_sample_exp` text,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        DB::statement("
            CREATE TABLE `table_downtimes` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `table_production_id` bigint unsigned DEFAULT NULL,
                `reporter` varchar(255) DEFAULT NULL,
                `group` varchar(255) DEFAULT NULL,
                `date` date DEFAULT NULL,
                `fy_n` varchar(255) DEFAULT NULL,
                `shift` varchar(255) DEFAULT NULL,
                `line` varchar(255) DEFAULT NULL,
                `model` varchar(255) DEFAULT NULL,
                `model_year` varchar(255) DEFAULT NULL,
                `item_name` varchar(255) DEFAULT NULL,
                `coil_no` varchar(255) DEFAULT NULL,
                `time_from` time DEFAULT NULL,
                `time_until` time DEFAULT NULL,
                `total_time` varchar(255) DEFAULT NULL,
                `process_name` varchar(255) DEFAULT NULL,
                `dt_category` varchar(255) DEFAULT NULL,
                `downtime_type` varchar(255) DEFAULT NULL,
                `dt_classification` varchar(255) DEFAULT NULL,
                `problem_description` text,
                `root_cause` text,
                `counter_measure` text,
                `pic` varchar(255) DEFAULT NULL,
                `status` varchar(255) DEFAULT NULL,
                `problem_picture` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `table_downtimes_table_production_id_foreign` (`table_production_id`),
                CONSTRAINT `table_downtimes_table_production_id_foreign` FOREIGN KEY (`table_production_id`) REFERENCES `table_productions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        DB::statement("
            CREATE TABLE `table_defects` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `table_production_id` bigint unsigned DEFAULT NULL,
                `reporter` varchar(255) DEFAULT NULL,
                `group` varchar(255) DEFAULT NULL,
                `date` date DEFAULT NULL,
                `fy_n` varchar(255) DEFAULT NULL,
                `shift` varchar(255) DEFAULT NULL,
                `line` varchar(255) DEFAULT NULL,
                `model` varchar(255) DEFAULT NULL,
                `model_year` varchar(255) DEFAULT NULL,
                `item_name` varchar(255) DEFAULT NULL,
                `coil_no` varchar(255) DEFAULT NULL,
                `defect_category` varchar(255) DEFAULT NULL,
                `defect_name` varchar(255) DEFAULT NULL,
                `defect_qty_a` int DEFAULT NULL,
                `defect_qty_b` int DEFAULT NULL,
                `defect_area` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `table_defects_table_production_id_foreign` (`table_production_id`),
                CONSTRAINT `table_defects_table_production_id_foreign` FOREIGN KEY (`table_production_id`) REFERENCES `table_productions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // 7. Create other essential tables
        DB::statement("
            CREATE TABLE `model_items` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `model_name` varchar(255) NOT NULL,
                `item_name` varchar(255) NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        DB::statement("
            CREATE TABLE `process_names` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `process_name` varchar(255) NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        DB::statement("
            CREATE TABLE `downtime_categories` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `dt_category` varchar(255) NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        DB::statement("
            CREATE TABLE `downtime_classifications` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `dt_classification` varchar(255) NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $results['production_tables_created'] = true;
        
        // 8. Mark all migrations as completed to prevent future conflicts
        $allMigrations = [
            '0001_01_01_000000_create_users_table',
            '0001_01_01_000001_create_cache_table',
            '0001_01_01_000002_create_jobs_table',
            '2025_05_04_213826_create_table_productions_table',
            '2025_06_30_061935_create_table_downtimes_table',
            '2025_07_15_055100_create_table_defects_table',
            '2024_08_03_094749_create_model_items_table',
            '2025_03_06_094315_create_process_names_table',
            '2024_08_18_223311_create_downtime_categories_table',
            '2025_03_14_060117_create_downtime_classifications_table'
        ];
        
        foreach ($allMigrations as $migration) {
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => 1
            ]);
        }
        
        // 9. Clear sessions directory
        $sessionPath = storage_path('framework/sessions');
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        // 10. Final verification
        $finalTables = DB::select('SHOW TABLES');
        $userCount = DB::table('users')->count();
        
        $results['final_tables'] = array_map(function($table) {
            return array_values((array) $table)[0];
        }, $finalTables);
        $results['user_count'] = $userCount;
        
        return response()->json([
            'status' => 'success',
            'message' => 'Complete application reset and fix completed successfully - All migration conflicts resolved',
            'results' => $results,
            'login_credentials' => [
                'email' => 'admin@email.com',
                'password' => 'aaaaa'
            ],
            'next_steps' => [
                '1. Login with credentials above',
                '2. Access dashboard to verify production tables',
                '3. Import production data from backup_production_data.sql'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Debug dashboard error specifically
Route::get('/debug-dashboard-error', function () {
    try {
        $results = [];
        
        // 1. Check if tables exist and have correct structure
        $tables = ['table_productions', 'table_downtimes', 'table_defects'];
        foreach ($tables as $table) {
            try {
                $exists = DB::select("SHOW TABLES LIKE '{$table}'");
                if ($exists) {
                    $columns = DB::select("DESCRIBE {$table}");
                    $results['tables'][$table] = [
                        'exists' => true,
                        'columns' => array_map(function($col) { return $col->Field; }, $columns),
                        'row_count' => DB::table($table)->count()
                    ];
                } else {
                    $results['tables'][$table] = ['exists' => false];
                }
            } catch (\Exception $e) {
                $results['tables'][$table] = [
                    'exists' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // 2. Test individual queries from DashboardController
        try {
            // Test TableDowntime query
            $nonProductiveTest = DB::table('table_downtimes')
                ->where(function ($query) {
                    $query->where('downtime_type', 'Non Productive Time')
                        ->orWhere('dt_category', 'trial');
                })
                ->select(
                    'fy_n',
                    'model', 
                    'item_name',
                    'date',
                    'shift',
                    'line',
                    'group',
                    DB::raw('SUM(total_time) as total_non_productive_downtime')
                )
                ->groupBy('fy_n', 'date', 'shift', 'model', 'item_name', 'line', 'group')
                ->limit(5)
                ->get();
            
            $results['queries']['non_productive_time'] = [
                'status' => 'success',
                'count' => count($nonProductiveTest),
                'sample' => $nonProductiveTest->take(2)
            ];
        } catch (\Exception $e) {
            $results['queries']['non_productive_time'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        try {
            // Test TableDefect query
            $defectTest = DB::table('table_defects')
                ->select(
                    'fy_n',
                    'model',
                    'item_name', 
                    'date',
                    'shift',
                    'line',
                    'group',
                    'defect_category',
                    'defect_name',
                    DB::raw('SUM(COALESCE(defect_qty_a, 0) + COALESCE(defect_qty_b, 0)) as total_defect')
                )
                ->whereNotNull('defect_category')
                ->groupBy('fy_n', 'model', 'item_name', 'date', 'shift', 'line', 'group', 'defect_category', 'defect_name')
                ->limit(5)
                ->get();
            
            $results['queries']['defect_data'] = [
                'status' => 'success', 
                'count' => count($defectTest),
                'sample' => $defectTest->take(2)
            ];
        } catch (\Exception $e) {
            $results['queries']['defect_data'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        try {
            // Test TableProduction query
            $productionTest = DB::table('table_productions')
                ->select(
                    'fy_n',
                    'model',
                    'item_name',
                    'date', 
                    'shift',
                    'line',
                    'group',
                    'ok_a',
                    'ok_b',
                    'plan_a',
                    'plan_b'
                )
                ->limit(5)
                ->get();
            
            $results['queries']['production_data'] = [
                'status' => 'success',
                'count' => count($productionTest),
                'sample' => $productionTest->take(2)
            ];
        } catch (\Exception $e) {
            $results['queries']['production_data'] = [
                'status' => 'error', 
                'message' => $e->getMessage()
            ];
        }
        
        // 3. Test if DashboardController can be instantiated
        try {
            $controller = new \App\Http\Controllers\DashboardController();
            $results['controller'] = [
                'instantiated' => true,
                'class' => get_class($controller)
            ];
        } catch (\Exception $e) {
            $results['controller'] = [
                'instantiated' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Dashboard error diagnosis completed',
            'results' => $results
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Simple dashboard test without complex queries
Route::get('/simple-dashboard-test', function () {
    try {
        // Test basic data access
        $tableProductions = DB::table('table_productions')->count();
        $tableDowntimes = DB::table('table_downtimes')->count(); 
        $tableDefects = DB::table('table_defects')->count();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Simple dashboard test passed',
            'data' => [
                'table_productions_count' => $tableProductions,
                'table_downtimes_count' => $tableDowntimes,
                'table_defects_count' => $tableDefects,
                'auth_user' => auth()->user() ? auth()->user()->email : 'not logged in'
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
