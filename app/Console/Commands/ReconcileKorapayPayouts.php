<?php

namespace App\Console\Commands;

use App\Models\DoctorPayment;
use App\Services\KoraPayPayoutService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileKorapayPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'korapay:reconcile-payouts {--limit=50 : Max processing records to reconcile} {--dry-run : Show changes without updating DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile local doctor payout records with Korapay verify API';

    /**
     * Execute the console command.
     */
    public function handle(KoraPayPayoutService $payoutService): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $payments = DoctorPayment::query()
            ->whereNotNull('korapay_reference')
            ->whereIn('status', ['pending', 'processing'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No pending/processing payouts to reconcile.');
            return self::SUCCESS;
        }

        $this->info("Reconciling {$payments->count()} payout(s)...");

        $updated = 0;
        $noChange = 0;
        $failedChecks = 0;

        foreach ($payments as $payment) {
            $verify = $payoutService->verifyPayoutStatus($payment->korapay_reference);

            if (!($verify['success'] ?? false)) {
                $failedChecks++;
                $this->warn("Verify failed for {$payment->reference} ({$payment->korapay_reference}): " . ($verify['message'] ?? 'Unknown error'));
                continue;
            }

            $remote = $verify['data'] ?? [];
            $remoteStatus = $remote['status'] ?? 'processing';

            $updateData = [
                'korapay_status' => $remoteStatus,
                'korapay_response' => $remote,
            ];

            if ($remoteStatus === 'success') {
                $updateData['status'] = 'completed';
                $updateData['transaction_reference'] = $remote['reference'] ?? $payment->korapay_reference;
                $updateData['payout_completed_at'] = now();
                $updateData['paid_at'] = $payment->paid_at ?? now();
                if (isset($remote['fee'])) {
                    $updateData['korapay_fee'] = (float) $remote['fee'];
                }
            } elseif ($remoteStatus === 'failed') {
                $updateData['status'] = 'failed';
                if (isset($remote['fee'])) {
                    $updateData['korapay_fee'] = (float) $remote['fee'];
                }
            } else {
                $updateData['status'] = 'processing';
            }

            $changed = ($payment->status !== $updateData['status']) || (($payment->korapay_status ?? null) !== $updateData['korapay_status']);

            if (!$changed) {
                $noChange++;
                $this->line("No change: {$payment->reference} [local={$payment->status}/{$payment->korapay_status}, remote={$remoteStatus}]");
                continue;
            }

            if ($dryRun) {
                $this->info("DRY-RUN update {$payment->reference}: {$payment->status}/{$payment->korapay_status} -> {$updateData['status']}/{$updateData['korapay_status']}");
                $updated++;
                continue;
            }

            $payment->update($updateData);
            $updated++;
            $this->info("Updated {$payment->reference}: {$payment->status}/{$payment->korapay_status} -> {$updateData['status']}/{$updateData['korapay_status']}");
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Updated', $updated],
                ['No Change', $noChange],
                ['Verify Failures', $failedChecks],
            ]
        );

        Log::info('Korapay payout reconciliation completed', [
            'limit' => $limit,
            'dry_run' => $dryRun,
            'updated' => $updated,
            'no_change' => $noChange,
            'failed_checks' => $failedChecks,
        ]);

        return self::SUCCESS;
    }
}

