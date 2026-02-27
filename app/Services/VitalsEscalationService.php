<?php

namespace App\Services;

use App\Models\VitalSign;
use App\Models\Patient;

/**
 * Evaluates vital sign readings against clinical thresholds
 * and returns escalation information for alerting.
 *
 * Does NOT send messages directly — that is handled by
 * EscalationAlertJob which uses VonageService.
 */
class VitalsEscalationService
{
    /**
     * Threshold configuration
     * Structure: [metric => [warning => [min,max], critical => [min,max]]]
     */
    protected array $thresholds = [
        'systolic' => [
            'warning'  => ['min' => 90, 'max' => 140],
            'critical' => ['min' => 80, 'max' => 180],
        ],
        'diastolic' => [
            'warning'  => ['min' => 60, 'max' => 90],
            'critical' => ['min' => 50, 'max' => 120],
        ],
        'heart_rate' => [
            'warning'  => ['min' => 50, 'max' => 100],
            'critical' => ['min' => 40, 'max' => 150],
        ],
        'oxygen_saturation' => [
            'warning'  => ['min' => 95, 'max' => 100],
            'critical' => ['min' => 90, 'max' => 100],
        ],
        'temperature' => [
            'warning'  => ['min' => 35.0, 'max' => 38.0],
            'critical' => ['min' => 34.0, 'max' => 40.0],
        ],
        'blood_sugar' => [
            'warning'  => ['min' => 70, 'max' => 180],
            'critical' => ['min' => 54, 'max' => 300],
        ],
    ];

    /**
     * Evaluate a VitalSign record and return escalation result.
     *
     * @return array{level: string, alerts: array, should_escalate: bool}
     */
    public function evaluate(VitalSign $vitalSign): array
    {
        $alerts = [];
        $highestLevel = 'normal';

        // Blood pressure
        if ($vitalSign->blood_pressure) {
            $parts = explode('/', $vitalSign->blood_pressure);
            if (count($parts) === 2) {
                $sys = (float) $parts[0];
                $dia = (float) $parts[1];

                $sysLevel = $this->checkThreshold('systolic', $sys);
                $diaLevel = $this->checkThreshold('diastolic', $dia);

                if ($sysLevel !== 'normal') {
                    $alerts[] = ['metric' => 'Blood Pressure (Systolic)', 'value' => $sys, 'level' => $sysLevel, 'unit' => 'mmHg'];
                }
                if ($diaLevel !== 'normal') {
                    $alerts[] = ['metric' => 'Blood Pressure (Diastolic)', 'value' => $dia, 'level' => $diaLevel, 'unit' => 'mmHg'];
                }

                $highestLevel = $this->highest($highestLevel, $sysLevel, $diaLevel);
            }
        }

        // Individual metrics
        $metrics = [
            'heart_rate'        => ['field' => 'heart_rate',        'label' => 'Heart Rate',        'unit' => 'bpm'],
            'oxygen_saturation' => ['field' => 'oxygen_saturation', 'label' => 'Oxygen Saturation', 'unit' => '%'],
            'temperature'       => ['field' => 'temperature',       'label' => 'Temperature',       'unit' => '°C'],
            'blood_sugar'       => ['field' => 'blood_sugar',       'label' => 'Blood Sugar',       'unit' => 'mg/dL'],
        ];

        foreach ($metrics as $key => $meta) {
            $value = $vitalSign->{$meta['field']};
            if ($value !== null) {
                $level = $this->checkThreshold($key, (float) $value);
                if ($level !== 'normal') {
                    $alerts[] = [
                        'metric' => $meta['label'],
                        'value' => $value,
                        'level' => $level,
                        'unit' => $meta['unit'],
                    ];
                }
                $highestLevel = $this->highest($highestLevel, $level);
            }
        }

        return [
            'level'           => $highestLevel,
            'alerts'          => $alerts,
            'should_escalate' => $highestLevel === 'critical',
        ];
    }

    /**
     * Build a human-readable alert summary for SMS / WhatsApp
     */
    public function buildAlertMessage(VitalSign $vitalSign, array $evaluation): string
    {
        $patient = $vitalSign->patient;
        $lines = [
            '🚨 DoctorOnTap — CRITICAL VITAL ALERT',
            '',
            'Patient: ' . ($patient->name ?? 'N/A'),
            'Time: ' . $vitalSign->created_at->format('d M Y H:i'),
            '',
        ];

        foreach ($evaluation['alerts'] as $alert) {
            $emoji = $alert['level'] === 'critical' ? '🔴' : '🟡';
            $lines[] = "{$emoji} {$alert['metric']}: {$alert['value']} {$alert['unit']} ({$alert['level']})";
        }

        $lines[] = '';
        $lines[] = 'Please review immediately.';

        return implode("\n", $lines);
    }

    /**
     * Check a single value against its threshold bracket
     */
    protected function checkThreshold(string $metric, float $value): string
    {
        $t = $this->thresholds[$metric] ?? null;
        if (!$t) {
            return 'normal';
        }

        // Critical: outside critical range
        if ($value < $t['critical']['min'] || $value > $t['critical']['max']) {
            return 'critical';
        }

        // Warning: outside warning range but within critical range
        if ($value < $t['warning']['min'] || $value > $t['warning']['max']) {
            return 'warning';
        }

        return 'normal';
    }

    /**
     * Return the highest severity level from given levels
     */
    protected function highest(string ...$levels): string
    {
        $priority = ['normal' => 0, 'warning' => 1, 'critical' => 2];
        $max = 'normal';
        foreach ($levels as $level) {
            if (($priority[$level] ?? 0) > ($priority[$max] ?? 0)) {
                $max = $level;
            }
        }
        return $max;
    }
}
