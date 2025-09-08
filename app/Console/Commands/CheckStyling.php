<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckStyling extends Command
{
    protected $signature = 'app:check-styling';
    protected $description = 'Check dashboard styling and create inline backup if needed';

    public function handle()
    {
        $this->info('=== STYLING DIAGNOSTICS ===');
        
        // Check if CSS files are readable
        $cssFiles = [
            'app-layout.css' => public_path('css/app-layout.css'),
            'dashboard-layout.css' => public_path('css/dashboard-layout.css'),
        ];
        
        foreach ($cssFiles as $name => $path) {
            if (File::exists($path)) {
                $content = File::get($path);
                $lines = count(explode("\n", $content));
                $this->info("✓ {$name}: {$lines} lines, " . strlen($content) . " characters");
                
                // Check for critical CSS classes
                if (str_contains($content, '.dashboard-container')) {
                    $this->info("  ✓ Contains .dashboard-container");
                } else {
                    $this->error("  ✗ Missing .dashboard-container");
                }
                
                if (str_contains($content, '.home-content')) {
                    $this->info("  ✓ Contains .home-content");
                } else {
                    $this->error("  ✗ Missing .home-content");
                }
            } else {
                $this->error("✗ {$name}: File not found");
            }
        }
        
        // Test asset URLs
        $this->info("\n=== ASSET URL TEST ===");
        $this->info("Base URL: " . url('/'));
        $this->info("CSS Asset: " . asset('css/app-layout.css'));
        $this->info("Environment: " . app()->environment());
        
        // Create inline CSS backup
        $this->info("\n=== CREATING INLINE CSS BACKUP ===");
        $this->createInlineBackup();
        
        return 0;
    }
    
    private function createInlineBackup()
    {
        $inlineCSS = "
<style>
/* Critical Dashboard Styles */
.dashboard-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    padding: 20px;
    margin-top: 20px;
}

.home-content {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    min-height: 300px;
}

.filter-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 48px;
    justify-content: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.filter-container select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
}

.home {
    margin-left: 250px;
    transition: all 0.3s ease;
    padding: 20px;
    min-height: 100vh;
    background: #f5f5f5;
}

.toggle-sidebar {
    position: fixed;
    top: 60px;
    left: 10px;
    z-index: 1000;
}

/* No data message */
.no-data-message {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 8px;
    margin: 20px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Chart containers */
canvas {
    max-width: 100%;
    height: auto !important;
}
</style>";

        $backupFile = resource_path('views/inline-dashboard-styles.blade.php');
        File::put($backupFile, $inlineCSS);
        
        $this->info("Inline CSS backup created at: {$backupFile}");
        $this->info("You can include this in dashboard.blade.php if external CSS fails to load");
    }
}
