<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwitchDashboardView extends Command
{
    protected $signature = 'app:switch-dashboard {mode=railway}';
    protected $description = 'Switch dashboard view between normal and railway version';

    public function handle()
    {
        $mode = $this->argument('mode');
        
        if ($mode === 'railway') {
            $this->info('Setting up Railway-optimized dashboard view...');
            
            // Update DashboardController to use railway view
            $controllerPath = app_path('Http/Controllers/DashboardController.php');
            $content = file_get_contents($controllerPath);
            
            // Replace the view name
            $newContent = str_replace(
                "return view('users.dashboard',",
                "return view('users.dashboard-railway',",
                $content
            );
            
            file_put_contents($controllerPath, $newContent);
            $this->info('✓ Dashboard controller updated to use Railway view');
            
        } else {
            $this->info('Reverting to normal dashboard view...');
            
            // Revert DashboardController to normal view
            $controllerPath = app_path('Http/Controllers/DashboardController.php');
            $content = file_get_contents($controllerPath);
            
            // Replace back
            $newContent = str_replace(
                "return view('users.dashboard-railway',",
                "return view('users.dashboard',",
                $content
            );
            
            file_put_contents($controllerPath, $newContent);
            $this->info('✓ Dashboard controller reverted to normal view');
        }
        
        $this->info('Dashboard view switch completed!');
        return 0;
    }
}
