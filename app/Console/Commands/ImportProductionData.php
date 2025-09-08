<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DowntimeClassification;
use App\Models\ProcessName;
use App\Models\ModelItem;
use App\Models\TableProduction;
use App\Models\TableDowntime;
use App\Models\TableDefect;
use Exception;

class ImportProductionData extends Command
{
    protected $signature = 'db:import-production';
    protected $description = 'Import production data from backup SQL to Railway database';

    public function handle()
    {
        $this->info('Starting production data import...');

        try {
            DB::beginTransaction();

            // Import Users
            $this->importUsers();
            
            // Import Reference Data
            $this->importDowntimeClassifications();
            $this->importProcessNames();
            $this->importModelItems();
            
            // Import Production Data
            $this->importTableProductions();
            $this->importTableDowntimes();
            $this->importTableDefects();

            DB::commit();
            $this->info('Production data import completed successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function importUsers()
    {
        $this->info('Importing users...');
        
        $users = [
            [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@email.com',
                'password' => '$2y$12$OiP2UF66w5DyZ2aomWPU3.bjeskEnr5HSs9dahYbVTXfCX/njCHae',
                'is_admin' => true,
                'created_at' => '2024-08-04 16:18:39',
                'updated_at' => '2024-08-04 16:18:39'
            ],
            [
                'id' => 3,
                'name' => 'user-1',
                'email' => 'user1@email.com',
                'password' => '$2y$12$G5S7YRWXyVeiVRn9vM3MquIEoq/CU2EjnWYgArZrwkY7X6qHziZYe',
                'is_admin' => false,
                'created_at' => '2024-08-08 14:36:14',
                'updated_at' => '2024-08-08 14:36:14'
            ],
            [
                'id' => 4,
                'name' => 'user-2',
                'email' => 'user2@email.com',
                'password' => '$2y$12$57e884szpuZk0t37q192rO.RW0FiZGubugQICmc0ztaLZQugX4n6.',
                'is_admin' => false,
                'created_at' => '2024-08-18 04:37:05',
                'updated_at' => '2024-08-18 04:37:05'
            ],
            [
                'id' => 5,
                'name' => 'user-3',
                'email' => 'user3@email.com',
                'password' => '$2y$12$Y/vPUKE5SYZdmQwgkcsEIeWR0w2MhJR6L68pqAb1RPi55OvV0dFQW',
                'is_admin' => false,
                'created_at' => '2025-02-08 02:54:04',
                'updated_at' => '2025-02-08 02:54:04'
            ]
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->info('Users imported successfully.');
    }

    private function importDowntimeClassifications()
    {
        $this->info('Importing downtime classifications...');
        
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
            ['id' => 11, 'downtime_classification' => 'Die Hit'],
            ['id' => 12, 'downtime_classification' => 'Shock Line'],
            ['id' => 13, 'downtime_classification' => 'Line Mark'],
            ['id' => 14, 'downtime_classification' => 'Die Interlock'],
            ['id' => 15, 'downtime_classification' => 'Scrap Stuck'],
            ['id' => 17, 'downtime_classification' => 'Waving'],
            ['id' => 18, 'downtime_classification' => 'Scrap Jump'],
        ];

        foreach ($classifications as $classification) {
            DowntimeClassification::updateOrCreate(
                ['id' => $classification['id']],
                $classification
            );
        }

        $this->info('Downtime classifications imported successfully.');
    }

    private function importProcessNames()
    {
        $this->info('Importing process names...');
        
        $processes = [
            ['id' => 1, 'process_name' => 'Line All'],
            ['id' => 2, 'process_name' => 'Crane Saver'],
            ['id' => 10, 'process_name' => 'Destack Feeder'],
            ['id' => 11, 'process_name' => 'Robot Destack Feeder'],
            ['id' => 12, 'process_name' => 'Entrance Conveyor'],
            ['id' => 13, 'process_name' => 'Washing Unit'],
            ['id' => 14, 'process_name' => 'Centering'],
            ['id' => 15, 'process_name' => 'Robot Loading'],
            ['id' => 16, 'process_name' => 'OP.10'],
            ['id' => 17, 'process_name' => 'Robot No.1'],
            ['id' => 18, 'process_name' => 'OP.20'],
            ['id' => 19, 'process_name' => 'Robot No.2'],
            ['id' => 20, 'process_name' => 'OP.30'],
            ['id' => 21, 'process_name' => 'Robot No.3'],
            ['id' => 22, 'process_name' => 'OP.40'],
            ['id' => 23, 'process_name' => 'Robot Unloading'],
            ['id' => 24, 'process_name' => 'Line End Conveyor'],
            ['id' => 25, 'process_name' => 'Checking Conveyor'],
            ['id' => 26, 'process_name' => 'Loading Panel'],
            ['id' => 27, 'process_name' => 'Stage-1 Conveyor'],
            ['id' => 28, 'process_name' => 'Stage-2 Conveyor'],
            ['id' => 29, 'process_name' => 'Stage-3 Conveyor'],
            ['id' => 30, 'process_name' => 'Stage-4 Conveyor'],
            ['id' => 31, 'process_name' => 'Stage-5 Conveyor'],
            ['id' => 33, 'process_name' => 'Traverser'],
            ['id' => 34, 'process_name' => 'Stage-6 Conveyor'],
        ];

        foreach ($processes as $process) {
            ProcessName::updateOrCreate(
                ['id' => $process['id']],
                $process
            );
        }

        $this->info('Process names imported successfully.');
    }

    private function importModelItems()
    {
        $this->info('Importing model items...');
        
        // Sample model items from backup
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
            ],
            [
                'id' => 4,
                'model_code' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'ITEM KEEMPAT',
                'product_picture' => '4.FFVV.2026.ITEM KEEMPAT.jpg'
            ],
            [
                'id' => 5,
                'model_code' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'ITEM KELIMA',
                'product_picture' => '5.FFVV.2026.ITEM KELIMA.jpg'
            ],
            [
                'id' => 6,
                'model_code' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'ITEM KEENAM',
                'product_picture' => '6.FFVV.2026.ITEM KEENAM.jpg'
            ]
        ];

        foreach ($items as $item) {
            ModelItem::updateOrCreate(
                ['id' => $item['id']],
                $item
            );
        }

        $this->info('Model items imported successfully.');
    }

    private function importTableProductions()
    {
        $this->info('Importing table productions...');
        
        // This would contain the actual production data from backup
        // For now, importing a few sample records
        $productions = [
            [
                'id' => 9,
                'reporter' => 'Joni',
                'group' => 'A',
                'date' => '2025-02-16',
                'fy_n' => 'FY2024-11',
                'shift' => 'day',
                'line' => 'Line-A',
                'start_time' => '12:12:00',
                'finish_time' => '14:17:00',
                'total_prod_time' => 125,
                'model' => 'FFVV',
                'model_year' => '2026',
                'spm' => 2.70,
                'item_name' => 'FFVV-ITEM PERTAMA',
                'coil_no' => 'XKCD\'s',
                'plan_a' => 14,
                'plan_b' => 14,
                'ok_a' => 511,
                'ok_b' => 511,
                'rework_a' => 8,
                'rework_b' => 8,
                'scrap_a' => 1,
                'scrap_b' => 1,
                'sample_a' => 1,
                'sample_b' => 1,
                'rework_exp' => 'gomi',
                'scrap_exp' => 'crack',
                'trial_sample_exp' => 'adjust CP'
            ]
        ];

        foreach ($productions as $production) {
            TableProduction::updateOrCreate(
                ['id' => $production['id']],
                $production
            );
        }

        $this->info('Table productions imported successfully.');
    }

    private function importTableDowntimes()
    {
        $this->info('Importing table downtimes...');
        
        // Sample downtime data
        $downtimes = [
            [
                'id' => 403,
                'table_production_id' => 9,
                'reporter' => 'Joni',
                'group' => 'A',
                'date' => '2025-02-16',
                'fy_n' => 'FY2024-11',
                'shift' => 'day',
                'line' => 'Line-A',
                'model' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'FFVV-ITEM PERTAMA',
                'coil_no' => 'XKCD\'s',
                'time_from' => '12:14:00',
                'time_until' => '12:19:00',
                'total_time' => 5,
                'process_name' => 'OP.30',
                'dt_category' => 'Operational',
                'downtime_type' => 'Downtime',
                'dt_classification' => 'Die Hit',
                'problem_description' => 'qqq',
                'root_cause' => 'www',
                'counter_measure' => 'eee',
                'pic' => 'tooling',
                'status' => 'open'
            ]
        ];

        foreach ($downtimes as $downtime) {
            TableDowntime::updateOrCreate(
                ['id' => $downtime['id']],
                $downtime
            );
        }

        $this->info('Table downtimes imported successfully.');
    }

    private function importTableDefects()
    {
        $this->info('Importing table defects...');
        
        // Sample defect data
        $defects = [
            [
                'id' => 300,
                'table_production_id' => 9,
                'reporter' => 'Joni',
                'group' => 'A',
                'date' => '2025-02-16',
                'fy_n' => 'FY2024-11',
                'shift' => 'day',
                'line' => 'Line-A',
                'model' => 'FFVV',
                'model_year' => '2026',
                'item_name' => 'FFVV-ITEM PERTAMA',
                'coil_no' => 'XKCD\'s',
                'defect_category' => 'outline',
                'defect_name' => 'Gomi',
                'defect_qty_a' => 8,
                'defect_qty_b' => null,
                'defect_area' => 'K7'
            ]
        ];

        foreach ($defects as $defect) {
            TableDefect::updateOrCreate(
                ['id' => $defect['id']],
                $defect
            );
        }

        $this->info('Table defects imported successfully.');
    }
}
