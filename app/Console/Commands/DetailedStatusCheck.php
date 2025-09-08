<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\TableProduction;
use App\Models\ModelItem;
use App\Models\DowntimeClassification;
use App\Models\ProcessName;

class DetailedStatusCheck extends Command
{
    protected $signature = 'debug:status';
    protected $description = 'Detailed status check for troubleshooting';

    public function handle()
    {
        $this->info('🔍 DETAILED STATUS CHECK');
        $this->info('========================');

        try {
            // Test database connection
            DB::connection()->getPdo();
            $this->info('✅ Database: Connected');

            // Check all tables
            $this->checkTable('users', User::class);
            $this->checkTable('table_productions', TableProduction::class);
            $this->checkTable('model_items', ModelItem::class);
            $this->checkTable('downtime_classifications', DowntimeClassification::class);
            $this->checkTable('process_names', ProcessName::class);

            // Test query that dashboard uses
            $this->info('');
            $this->info('🧪 Testing Dashboard Query...');
            
            $chartData = TableProduction::select('fy_n', 'model', 'item_name', 'date', 'shift', 'line', 'group')
                ->selectRaw('SUM(COALESCE(ok_a, 0)) as total_ok_a')
                ->whereNotNull('fy_n')
                ->whereNotNull('date')
                ->groupBy('fy_n', 'date', 'shift', 'model', 'item_name', 'line', 'group')
                ->get();

            $this->info("Query Result: {$chartData->count()} records found");
            
            if ($chartData->count() > 0) {
                $sample = $chartData->first();
                $this->info("Sample Data: FY={$sample->fy_n}, Model={$sample->model}, Item={$sample->item_name}");
                $this->info('🎉 Dashboard should show data!');
            } else {
                $this->warn('⚠️  Query returned empty - this is why dashboard shows "No Production Data"');
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }

        return 0;
    }

    private function checkTable($tableName, $modelClass)
    {
        try {
            $count = $modelClass::count();
            if ($count > 0) {
                $this->info("✅ {$tableName}: {$count} records");
                
                // Show sample data
                $sample = $modelClass::first();
                if ($tableName === 'users') {
                    $this->info("   Sample: {$sample->name} ({$sample->email})");
                } elseif ($tableName === 'table_productions') {
                    $this->info("   Sample: {$sample->fy_n} - {$sample->item_name}");
                }
            } else {
                $this->warn("⚠️  {$tableName}: 0 records (EMPTY)");
            }
        } catch (\Exception $e) {
            $this->error("❌ {$tableName}: Error - " . $e->getMessage());
        }
    }
}
