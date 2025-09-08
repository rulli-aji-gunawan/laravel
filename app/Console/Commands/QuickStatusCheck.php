<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\TableProduction;

class QuickStatusCheck extends Command
{
    protected $signature = 'app:status';
    protected $description = 'Quick status check for Railway deployment';

    public function handle()
    {
        $this->info('🚀 Railway App Status Check');
        $this->info('===========================');

        try {
            // Database connection test
            DB::connection()->getPdo();
            $this->info('✅ Database: Connected');

            // Check users
            $userCount = User::count();
            $this->info("✅ Users: {$userCount} found");
            
            if ($userCount > 0) {
                $admin = User::where('is_admin', true)->first();
                if ($admin) {
                    $this->info("✅ Admin User: {$admin->email}");
                } else {
                    $this->warn("⚠️  No admin user found");
                }
            }

            // Check production data
            $prodCount = TableProduction::count();
            $this->info("✅ Production Records: {$prodCount}");

            // Overall status
            if ($userCount > 0 && $prodCount > 0) {
                $this->info('🎉 Status: Ready for use!');
                $this->info('📊 Dashboard should show charts and data');
            } elseif ($userCount > 0 && $prodCount == 0) {
                $this->warn('⚠️  Status: Users OK, but no production data');
                $this->warn('📊 Dashboard will show "No Production Data Available"');
                $this->info('💡 Run: php artisan db:setup-minimal');
            } else {
                $this->error('❌ Status: Missing users and data');
                $this->info('💡 Run: php artisan db:setup-minimal');
            }

        } catch (\Exception $e) {
            $this->error('❌ Database: Connection failed');
            $this->error('Error: ' . $e->getMessage());
        }

        $this->info('===========================');
        return 0;
    }
}
