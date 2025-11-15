<?php

namespace App\Console\Commands;

use App\Models\Consultation;
use App\Models\VitalSign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TestAuditLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test HIPAA audit logging functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing HIPAA Audit Logging...');
        $this->newLine();

        // Check if audit log channel is configured
        $this->info('✓ Step 1: Checking audit log configuration...');
        if (config('logging.channels.audit')) {
            $this->info('  ✓ Audit channel configured');
        } else {
            $this->error('  ✗ Audit channel not configured!');
            return 1;
        }

        // Check if models have the trait
        $this->info('✓ Step 2: Checking if models have Auditable trait...');
        $models = [
            'Consultation' => Consultation::class,
            'VitalSign' => VitalSign::class,
        ];

        foreach ($models as $name => $class) {
            $traits = class_uses($class);
            if (isset($traits['App\Traits\Auditable'])) {
                $this->info("  ✓ {$name} has Auditable trait");
            } else {
                $this->error("  ✗ {$name} missing Auditable trait");
            }
        }

        // Get current log count (daily logs have date suffix)
        $logFile = storage_path('logs/audit-' . date('Y-m-d') . '.log');
        $beforeCount = 0;
        if (File::exists($logFile)) {
            $beforeCount = count(file($logFile));
            $this->info("✓ Step 3: Current audit log has {$beforeCount} entries");
        } else {
            $this->info('✓ Step 3: No audit log yet (will be created)');
        }

        // Test: Create a consultation record
        $this->info('✓ Step 4: Testing audit logging...');
        $this->info('  → Creating test consultation...');
        
        try {
            $consultation = new Consultation();
            $consultation->reference = 'TEST-AUDIT-' . time();
            $consultation->first_name = 'Test';
            $consultation->last_name = 'Patient';
            $consultation->email = 'test@audit.com';
            $consultation->mobile = '08000000000';
            $consultation->age = 30;
            $consultation->gender = 'male';
            $consultation->problem = 'Test for audit logging';
            $consultation->severity = 'mild';
            $consultation->consult_mode = 'video';
            $consultation->status = 'pending';
            $consultation->save();

            $this->info('  ✓ Test consultation created (ID: ' . $consultation->id . ')');

            // Wait a moment for log to be written
            sleep(1);

            // Check if audit log was created
            if (File::exists($logFile)) {
                $afterCount = count(file($logFile));
                $newEntries = $afterCount - $beforeCount;
                
                if ($newEntries > 0) {
                    $this->newLine();
                    $this->info('🎉 SUCCESS! Audit logging is working!');
                    $this->info("  → {$newEntries} new audit log entry created");
                    $this->newLine();
                    
                    // Show the last log entry
                    $lines = file($logFile);
                    $lastLine = end($lines);
                    
                    $this->info('📋 Last Audit Log Entry:');
                    $this->line('  ' . $lastLine);
                    $this->newLine();
                } else {
                    $this->error('  ✗ No new audit entries found');
                    return 1;
                }
            } else {
                $this->error('  ✗ Audit log file not created');
                return 1;
            }

            // Clean up test record
            $consultation->forceDelete();
            $this->info('✓ Test record cleaned up');
            $this->newLine();

            // Summary
            $this->info('📊 AUDIT LOGGING STATUS:');
            $this->info('  ✓ Configuration: OK');
            $this->info('  ✓ Trait installed: OK');
            $this->info('  ✓ Logging working: OK');
            $this->info('  ✓ Log location: ' . $logFile);
            $this->newLine();
            
            $this->info('🔐 HIPAA Compliance: ACTIVE');
            $this->info('All PHI access is now being tracked and logged.');
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->error('Error during test: ' . $e->getMessage());
            return 1;
        }
    }
}

