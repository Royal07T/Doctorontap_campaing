<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-performance {--force : Force optimization even in non-production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize application for maximum performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !app()->environment('production')) {
            $this->warn('⚠️  This command is meant for production environments.');
            if (!$this->confirm('Do you want to continue anyway?')) {
                $this->info('Optimization cancelled.');
                return 0;
            }
        }

        $this->info('🚀 Starting performance optimization...');
        $this->newLine();

        // Clear all caches first
        $this->info('⏳ Clearing existing caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $this->line('   ✓ Caches cleared');

        // Optimize configuration
        $this->info('⏳ Caching configuration...');
        Artisan::call('config:cache');
        $this->line('   ✓ Configuration cached');

        // Optimize routes
        $this->info('⏳ Caching routes...');
        Artisan::call('route:cache');
        $this->line('   ✓ Routes cached');

        // Optimize views
        $this->info('⏳ Compiling views...');
        Artisan::call('view:cache');
        $this->line('   ✓ Views compiled');

        // Optimize events
        $this->info('⏳ Caching events...');
        Artisan::call('event:cache');
        $this->line('   ✓ Events cached');

        // Optimize autoloader
        $this->info('⏳ Optimizing autoloader...');
        exec('composer dump-autoload -o 2>&1', $output, $returnCode);
        if ($returnCode === 0) {
            $this->line('   ✓ Autoloader optimized');
        } else {
            $this->warn('   ⚠  Autoloader optimization failed');
        }

        // Build assets if Vite is available
        if (file_exists(base_path('package.json'))) {
            $this->info('⏳ Building optimized assets...');
            exec('npm run build 2>&1', $output, $returnCode);
            if ($returnCode === 0) {
                $this->line('   ✓ Assets built');
            } else {
                $this->warn('   ⚠  Asset build failed');
            }
        }

        $this->newLine();
        $this->info('✅ Performance optimization complete!');
        $this->newLine();

        // Show optimization tips
        $this->comment('💡 Additional tips for maximum performance:');
        $this->line('   • Enable OPcache in your PHP configuration');
        $this->line('   • Use a CDN for static assets');
        $this->line('   • Enable Redis/Memcached for caching');
        $this->line('   • Configure your web server (Apache/Nginx) for gzip compression');
        $this->line('   • Consider using a reverse proxy (Varnish/CloudFlare)');
        
        return 0;
    }
}

