<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\TableProduction;
use App\Models\DowntimeClassification;
use App\Models\ProcessName;
use App\Models\ModelItem;

class TestDatabaseConnection extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Test database connection and check if tables exist';

    public function handle()
    {
        $this->info('Testing database connection...');

        try {
            // Test basic connection
            DB::connection()->getPdo();
            $this->info('✓ Database connection successful');

            // Check if tables exist
            $tables = [
                'users' => User::class,
                'table_productions' => TableProduction::class,
                'downtime_classifications' => DowntimeClassification::class,
                'process_names' => ProcessName::class,
                'model_items' => ModelItem::class,
            ];

            foreach ($tables as $tableName => $modelClass) {
                try {
                    $count = $modelClass::count();
                    $this->info("✓ Table {$tableName}: {$count} records");
                } catch (\Exception $e) {
                    $this->error("✗ Table {$tableName}: " . $e->getMessage());
                }
            }

            // Test creating a sample production record if none exist
            if (TableProduction::count() == 0) {
                $this->info('Creating sample production data...');
                
                TableProduction::create([
                    'reporter' => 'Test User',
                    'group' => 'A',
                    'date' => now()->format('Y-m-d'),
                    'fy_n' => 'FY25-6',
                    'shift' => 'day',
                    'line' => 'Line-A',
                    'start_time' => '08:00:00',
                    'finish_time' => '16:00:00',
                    'total_prod_time' => 480,
                    'model' => 'TEST',
                    'model_year' => '2025',
                    'spm' => 10.5,
                    'item_name' => 'TEST-ITEM',
                    'coil_no' => 'TEST123',
                    'plan_a' => 100,
                    'plan_b' => 100,
                    'ok_a' => 95,
                    'ok_b' => 95,
                    'rework_a' => 3,
                    'rework_b' => 3,
                    'scrap_a' => 2,
                    'scrap_b' => 2,
                    'sample_a' => 0,
                    'sample_b' => 0,
                    'rework_exp' => 'minor defects',
                    'scrap_exp' => 'material issues',
                    'trial_sample_exp' => 'none'
                ]);
                
                $this->info('✓ Sample production record created');
            }

            $this->info('Database test completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
            return 1;
        }
    }
}
