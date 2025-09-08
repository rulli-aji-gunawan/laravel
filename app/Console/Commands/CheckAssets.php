<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckAssets extends Command
{
    protected $signature = 'app:check-assets';
    protected $description = 'Check if CSS and JS assets exist and are accessible';

    public function handle()
    {
        $this->info('=== ASSET CHECK ===');
        
        // CSS files to check
        $cssFiles = [
            'css/app-layout.css',
            'css/dashboard-layout.css',
            'css/input-production-layout.css'
        ];
        
        // JS files to check
        $jsFiles = [
            'js/sph-chart.js',
            'js/or-chart.js',
            'js/ftc-chart.js',
            'js/rr-chart.js',
            'js/sr-chart.js',
            'js/defect-chart.js',
            'js/sidebar.js'
        ];
        
        $this->info('Checking CSS files:');
        foreach ($cssFiles as $file) {
            $path = public_path($file);
            if (File::exists($path)) {
                $size = File::size($path);
                $this->info("✓ {$file} - {$size} bytes");
            } else {
                $this->error("✗ {$file} - NOT FOUND");
            }
        }
        
        $this->info("\nChecking JS files:");
        foreach ($jsFiles as $file) {
            $path = public_path($file);
            if (File::exists($path)) {
                $size = File::size($path);
                $this->info("✓ {$file} - {$size} bytes");
            } else {
                $this->error("✗ {$file} - NOT FOUND");
            }
        }
        
        // Test asset URL generation
        $this->info("\nTesting asset URLs:");
        $this->info("CSS URL: " . asset('css/app-layout.css'));
        $this->info("JS URL: " . asset('js/sph-chart.js'));
        
        // Check if we're in production
        $this->info("\nEnvironment info:");
        $this->info("APP_ENV: " . env('APP_ENV'));
        $this->info("APP_URL: " . env('APP_URL'));
        $this->info("Asset URL: " . config('app.asset_url', 'Not set'));
        
        return 0;
    }
}
