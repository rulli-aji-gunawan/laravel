<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\TableProduction;
use App\Models\DowntimeClassification;
use App\Models\ProcessName;
use App\Models\ModelItem;
use App\Models\TableDowntime;
use App\Models\TableDefect;
use Illuminate\Support\Facades\Hash;

class SetupMinimalData extends Command
{
    protected $signature = 'db:setup-minimal';
    protected $description = 'Setup minimal data for Railway deployment';

    public function handle()
    {
        $this->info('Setting up minimal data for Railway deployment...');

        try {
            DB::beginTransaction();

            // 1. Setup Admin User
            $this->setupAdminUser();
            
            // 2. Setup Master Data
            $this->setupMasterData();
            
            // 3. Setup Sample Production Data
            $this->setupSampleData();

            DB::commit();
            $this->info('✓ Minimal data setup completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('✗ Setup failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function setupAdminUser()
    {
        $this->info('Setting up admin user...');
        
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@email.com',
                'password' => Hash::make('aaaaa'),
                'is_admin' => true,
            ]
        );

        $this->info('✓ Admin user created: admin@email.com / aaaaa');
    }

    private function setupMasterData()
    {
        $this->info('Setting up master data...');

        // Setup Downtime Classifications
        $classifications = [
            ['id' => 1, 'downtime_classification' => 'Planned Downtime'],
            ['id' => 2, 'downtime_classification' => 'Gomi'],
            ['id' => 3, 'downtime_classification' => 'Kiriko'],
            ['id' => 5, 'downtime_classification' => 'Dent'],
            ['id' => 6, 'downtime_classification' => 'Scratch'],
            ['id' => 7, 'downtime_classification' => 'Crack'],
            ['id' => 8, 'downtime_classification' => 'Necking'],
            ['id' => 9, 'downtime_classification' => 'Burry'],
            ['id' => 10, 'downtime_classification' => 'Ding'],
        ];

        foreach ($classifications as $classification) {
            DowntimeClassification::updateOrCreate(
                ['id' => $classification['id']],
                $classification
            );
        }

        // Setup Process Names
        $processes = [
            ['id' => 1, 'process_name' => 'Line All'],
            ['id' => 16, 'process_name' => 'OP.10'],
            ['id' => 18, 'process_name' => 'OP.20'],
            ['id' => 20, 'process_name' => 'OP.30'],
            ['id' => 22, 'process_name' => 'OP.40'],
            ['id' => 17, 'process_name' => 'Robot No.1'],
            ['id' => 19, 'process_name' => 'Robot No.2'],
        ];

        foreach ($processes as $process) {
            ProcessName::updateOrCreate(
                ['id' => $process['id']],
                $process
            );
        }

        // Setup Model Items
        $items = [
            [
                'id' => 1,
                'model_code' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'ITEM PERTAMA',
                'product_picture' => '1.FFVV.2026.ITEM PERTAMA.jpg'
            ],
            [
                'id' => 2,
                'model_code' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'ITEM KEDUA',
                'product_picture' => '2.FFVV.2026.ITEM KEDUA.jpg'
            ],
            [
                'id' => 3,
                'model_code' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'ITEM KETIGA',
                'product_picture' => '3.FFVV.2026.ITEM KETIGA.jpg'
            ]
        ];

        foreach ($items as $item) {
            ModelItem::updateOrCreate(
                ['id' => $item['id']],
                $item
            );
        }

        $this->info('✓ Master data created');
    }

    private function setupSampleData()
    {
        $this->info('Setting up sample production data...');

        // Create sample production records
        $productions = [
            [
                'id' => 1,
                'reporter' => 'Admin User',
                'group' => 'A',
                'date' => '2025-09-08',
                'fy_n' => 'FY25-6',
                'shift' => 'day',
                'line' => 'Line-A',
                'start_time' => '08:00:00',
                'finish_time' => '16:00:00',
                'total_prod_time' => 480,
                'model' => 'FFVV',
                'model_year' => '2026',
                'spm' => 10.5,
                'item_name' => 'FFVV-ITEM PERTAMA',
                'coil_no' => 'SAMPLE001',
                'plan_a' => 500,
                'plan_b' => 500,
                'ok_a' => 475,
                'ok_b' => 470,
                'rework_a' => 15,
                'rework_b' => 18,
                'scrap_a' => 10,
                'scrap_b' => 12,
                'sample_a' => 0,
                'sample_b' => 0,
                'rework_exp' => 'Minor surface defects',
                'scrap_exp' => 'Material crack',
                'trial_sample_exp' => 'None'
            ],
            [
                'id' => 2,
                'reporter' => 'Admin User',
                'group' => 'B',
                'date' => '2025-09-08',
                'fy_n' => 'FY25-6',
                'shift' => 'night',
                'line' => 'Line-A',
                'start_time' => '20:00:00',
                'finish_time' => '04:00:00',
                'total_prod_time' => 480,
                'model' => 'FFVV',
                'model_year' => '2026',
                'spm' => 9.8,
                'item_name' => 'FFVV-ITEM KEDUA',
                'coil_no' => 'SAMPLE002',
                'plan_a' => 450,
                'plan_b' => 450,
                'ok_a' => 430,
                'ok_b' => 425,
                'rework_a' => 12,
                'rework_b' => 15,
                'scrap_a' => 8,
                'scrap_b' => 10,
                'sample_a' => 0,
                'sample_b' => 0,
                'rework_exp' => 'Dimensional issues',
                'scrap_exp' => 'Burry defects',
                'trial_sample_exp' => 'None'
            ]
        ];

        foreach ($productions as $production) {
            TableProduction::updateOrCreate(
                ['id' => $production['id']],
                $production
            );
        }

        // Create sample downtime records
        $downtimes = [
            [
                'table_production_id' => 1,
                'reporter' => 'Admin User',
                'group' => 'A',
                'date' => '2025-09-08',
                'fy_n' => 'FY25-6',
                'shift' => 'day',
                'line' => 'Line-A',
                'model' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'FFVV-ITEM PERTAMA',
                'coil_no' => 'SAMPLE001',
                'time_from' => '10:15:00',
                'time_until' => '10:30:00',
                'total_time' => 15,
                'process_name' => 'OP.10',
                'dt_category' => 'Equipment',
                'downtime_type' => 'Downtime',
                'dt_classification' => 'Crack',
                'problem_description' => 'Die crack detected',
                'root_cause' => 'Material hardness',
                'counter_measure' => 'Die replacement',
                'pic' => 'tooling',
                'status' => 'close'
            ]
        ];

        foreach ($downtimes as $downtime) {
            TableDowntime::create($downtime);
        }

        // Create sample defect records
        $defects = [
            [
                'table_production_id' => 1,
                'reporter' => 'Admin User',
                'group' => 'A',
                'date' => '2025-09-08',
                'fy_n' => 'FY25-6',
                'shift' => 'day',
                'line' => 'Line-A',
                'model' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'FFVV-ITEM PERTAMA',
                'coil_no' => 'SAMPLE001',
                'defect_category' => 'inline',
                'defect_name' => 'Gomi',
                'defect_qty_a' => 5,
                'defect_qty_b' => 3,
                'defect_area' => 'K7'
            ]
        ];

        foreach ($defects as $defect) {
            TableDefect::create($defect);
        }

        $this->info('✓ Sample data created');
    }
}
