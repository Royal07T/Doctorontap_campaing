<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigratePublicDocumentsToPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hipaa:migrate-documents 
                            {--dry-run : Show what would be done without actually moving files}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate medical documents from public storage to private storage for HIPAA compliance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        $this->info('🔒 HIPAA Medical Document Migration');
        $this->newLine();
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No files will be moved');
            $this->newLine();
        }
        
        // Source and destination paths
        $publicPath = storage_path('app/public/medical_documents');
        $privatePath = storage_path('app/private/medical_documents');
        
        // Check if source directory exists
        if (!File::exists($publicPath)) {
            $this->info('✓ No public medical_documents directory found.');
            $this->info('  This is good - files are already in private storage or don\'t exist yet.');
            return 0;
        }
        
        // Get all files in public storage
        $files = File::files($publicPath);
        
        if (empty($files)) {
            $this->info('✓ No files found in public/medical_documents directory.');
            $this->info('  Nothing to migrate.');
            
            if (!$dryRun) {
                $this->info('  Removing empty public directory...');
                File::deleteDirectory($publicPath);
                $this->info('✓ Empty directory removed.');
            }
            
            return 0;
        }
        
        // Show what will be migrated
        $this->info('📋 Found ' . count($files) . ' file(s) to migrate:');
        $this->newLine();
        
        $totalSize = 0;
        foreach ($files as $file) {
            $size = $file->getSize();
            $totalSize += $size;
            $this->line('  • ' . $file->getFilename() . ' (' . number_format($size / 1024, 2) . ' KB)');
        }
        
        $this->newLine();
        $this->info('Total size: ' . number_format($totalSize / 1024 / 1024, 2) . ' MB');
        $this->newLine();
        
        // Confirm migration
        if (!$force && !$dryRun) {
            if (!$this->confirm('Do you want to proceed with migrating these files to private storage?')) {
                $this->warn('Migration cancelled.');
                return 1;
            }
            $this->newLine();
        }
        
        // Create private directory if it doesn't exist
        if (!$dryRun) {
            if (!File::exists($privatePath)) {
                File::makeDirectory($privatePath, 0755, true);
                $this->info('✓ Created private/medical_documents directory');
            }
        }
        
        // Migrate files
        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;
        
        $this->info('🚀 Starting migration...');
        $this->newLine();
        
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $sourcePath = $file->getPathname();
            $destinationPath = $privatePath . '/' . $filename;
            
            try {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Would move: $filename");
                    $successCount++;
                } else {
                    // Check if file already exists in destination
                    if (File::exists($destinationPath)) {
                        $this->warn("  ⚠ Skipped: $filename (already exists in private storage)");
                        $skippedCount++;
                        continue;
                    }
                    
                    // Move file
                    File::move($sourcePath, $destinationPath);
                    $this->info("  ✓ Migrated: $filename");
                    $successCount++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Failed: $filename - " . $e->getMessage());
                $failedCount++;
            }
        }
        
        $this->newLine();
        
        // Summary
        $this->info('📊 Migration Summary:');
        $this->info('  ✓ Success: ' . $successCount);
        
        if ($skippedCount > 0) {
            $this->warn('  ⚠ Skipped: ' . $skippedCount);
        }
        
        if ($failedCount > 0) {
            $this->error('  ✗ Failed: ' . $failedCount);
        }
        
        $this->newLine();
        
        // Clean up empty public directory
        if (!$dryRun && $failedCount === 0 && File::exists($publicPath)) {
            $remainingFiles = File::files($publicPath);
            if (empty($remainingFiles)) {
                File::deleteDirectory($publicPath);
                $this->info('✓ Removed empty public/medical_documents directory');
                $this->newLine();
            }
        }
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN COMPLETE - No files were actually moved');
            $this->info('Run without --dry-run to perform the actual migration');
        } else {
            $this->info('🎉 Migration complete!');
            $this->newLine();
            $this->info('Next steps:');
            $this->info('  1. Test file downloads from the application');
            $this->info('  2. Verify old public URLs no longer work');
            $this->info('  3. Verify new secure download links work with authentication');
        }
        
        return $failedCount > 0 ? 1 : 0;
    }
}

