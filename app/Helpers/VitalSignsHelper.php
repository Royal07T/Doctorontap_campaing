<?php

namespace App\Helpers;

class VitalSignsHelper
{
    /**
     * Check if vital signs require medical attention
     *
     * @param object $vitalSign
     * @return array ['needsAttention' => bool, 'alerts' => array]
     */
    public static function checkForAlerts($vitalSign): array
    {
        $alerts = [];
        
        // Blood Pressure Check
        if ($vitalSign->blood_pressure) {
            $bp = self::parseBloodPressure($vitalSign->blood_pressure);
            if ($bp) {
                if ($bp['systolic'] >= 180 || $bp['diastolic'] >= 120) {
                    $alerts[] = [
                        'type' => 'critical',
                        'vital' => 'Blood Pressure',
                        'value' => $vitalSign->blood_pressure . ' mmHg',
                        'message' => 'Your blood pressure reading is quite high. Let\'s get you checked by a doctor today to keep you safe and healthy.',
                        'icon' => '💙'
                    ];
                } elseif ($bp['systolic'] >= 140 || $bp['diastolic'] >= 90) {
                    $alerts[] = [
                        'type' => 'warning',
                        'vital' => 'Blood Pressure',
                        'value' => $vitalSign->blood_pressure . ' mmHg',
                        'message' => 'Your blood pressure is a bit elevated. A quick chat with a doctor can help you manage this easily.',
                        'icon' => '💙'
                    ];
                } elseif ($bp['systolic'] < 90 || $bp['diastolic'] < 60) {
                    $alerts[] = [
                        'type' => 'warning',
                        'vital' => 'Blood Pressure',
                        'value' => $vitalSign->blood_pressure . ' mmHg',
                        'message' => 'Your blood pressure is on the lower side. Let\'s make sure everything is okay - a doctor can help.',
                        'icon' => '💙'
                    ];
                }
            }
        }
        
        // Heart Rate Check
        if ($vitalSign->heart_rate) {
            if ($vitalSign->heart_rate > 100) {
                $alerts[] = [
                    'type' => $vitalSign->heart_rate > 120 ? 'critical' : 'warning',
                    'vital' => 'Heart Rate',
                    'value' => $vitalSign->heart_rate . ' bpm',
                    'message' => $vitalSign->heart_rate > 120 ? 
                        'Your heart is working extra hard. Let\'s have a doctor check this out today to ensure you\'re okay.' : 
                        'Your heart rate is a bit fast. A quick consultation can help you understand why and what to do.',
                    'icon' => '❤️'
                ];
            } elseif ($vitalSign->heart_rate < 60) {
                $alerts[] = [
                    'type' => $vitalSign->heart_rate < 40 ? 'critical' : 'warning',
                    'vital' => 'Heart Rate',
                    'value' => $vitalSign->heart_rate . ' bpm',
                    'message' => $vitalSign->heart_rate < 40 ? 
                        'Your heart rate is quite slow. Let\'s have a doctor take a look to make sure everything is fine.' : 
                        'Your heart rate is slower than usual. If you\'re feeling okay, a routine check-up would be helpful.',
                    'icon' => '❤️'
                ];
            }
        }
        
        // Temperature Check
        if ($vitalSign->temperature) {
            if ($vitalSign->temperature >= 39.0) {
                $alerts[] = [
                    'type' => 'critical',
                    'vital' => 'Temperature',
                    'value' => $vitalSign->temperature . '°C',
                    'message' => 'You have a high fever. Let\'s get you feeling better - talk to a doctor today for proper care.',
                    'icon' => '🌡️'
                ];
            } elseif ($vitalSign->temperature >= 37.5) {
                $alerts[] = [
                    'type' => 'warning',
                    'vital' => 'Temperature',
                    'value' => $vitalSign->temperature . '°C',
                    'message' => 'You have a mild fever. Rest up, stay hydrated, and let a doctor know if it doesn\'t improve.',
                    'icon' => '🌡️'
                ];
            } elseif ($vitalSign->temperature < 35.0) {
                $alerts[] = [
                    'type' => 'critical',
                    'vital' => 'Temperature',
                    'value' => $vitalSign->temperature . '°C',
                    'message' => 'Your body temperature is quite low. Let\'s warm you up and get a doctor to check on you.',
                    'icon' => '🌡️'
                ];
            }
        }
        
        // Blood Sugar Check
        if ($vitalSign->blood_sugar) {
            if ($vitalSign->blood_sugar >= 200) {
                $alerts[] = [
                    'type' => 'critical',
                    'vital' => 'Blood Sugar',
                    'value' => $vitalSign->blood_sugar . ' mg/dL',
                    'message' => 'Your blood sugar is quite high. Let\'s talk to a doctor today to help you manage this properly.',
                    'icon' => '🩸'
                ];
            } elseif ($vitalSign->blood_sugar >= 140) {
                $alerts[] = [
                    'type' => 'warning',
                    'vital' => 'Blood Sugar',
                    'value' => $vitalSign->blood_sugar . ' mg/dL',
                    'message' => 'Your blood sugar is a bit elevated. A simple check-up can help you stay healthy and prevent issues.',
                    'icon' => '🩸'
                ];
            } elseif ($vitalSign->blood_sugar < 70) {
                $alerts[] = [
                    'type' => 'critical',
                    'vital' => 'Blood Sugar',
                    'value' => $vitalSign->blood_sugar . ' mg/dL',
                    'message' => 'Your blood sugar is low - have something sweet to eat, then let\'s check in with a doctor.',
                    'icon' => '🩸'
                ];
            }
        }
        
        // Oxygen Saturation Check
        if ($vitalSign->oxygen_saturation) {
            if ($vitalSign->oxygen_saturation < 90) {
                $alerts[] = [
                    'type' => 'critical',
                    'vital' => 'Oxygen Saturation',
                    'value' => $vitalSign->oxygen_saturation . '%',
                    'message' => 'Your oxygen level is lower than we\'d like. Let\'s get you checked by a doctor right away.',
                    'icon' => '🫁'
                ];
            } elseif ($vitalSign->oxygen_saturation < 95) {
                $alerts[] = [
                    'type' => 'warning',
                    'vital' => 'Oxygen Saturation',
                    'value' => $vitalSign->oxygen_saturation . '%',
                    'message' => 'Your oxygen level could be better. A quick consultation will help ensure you\'re breathing easy.',
                    'icon' => '🫁'
                ];
            }
        }
        
        // Respiratory Rate Check
        if ($vitalSign->respiratory_rate) {
            if ($vitalSign->respiratory_rate > 24) {
                $alerts[] = [
                    'type' => $vitalSign->respiratory_rate > 30 ? 'critical' : 'warning',
                    'vital' => 'Respiratory Rate',
                    'value' => $vitalSign->respiratory_rate . ' breaths/min',
                    'message' => $vitalSign->respiratory_rate > 30 ? 
                        'You\'re breathing faster than normal. Let\'s have a doctor check this out today.' : 
                        'Your breathing is a bit quick. A consultation can help you understand why and find relief.',
                    'icon' => '🫁'
                ];
            } elseif ($vitalSign->respiratory_rate < 12) {
                $alerts[] = [
                    'type' => 'warning',
                    'vital' => 'Respiratory Rate',
                    'value' => $vitalSign->respiratory_rate . ' breaths/min',
                    'message' => 'Your breathing rate is slower than usual. If you feel fine, a check-up would still be good.',
                    'icon' => '🫁'
                ];
            }
        }
        
        // BMI Check
        if ($vitalSign->bmi) {
            if ($vitalSign->bmi < 18.5) {
                $alerts[] = [
                    'type' => 'info',
                    'vital' => 'BMI',
                    'value' => number_format($vitalSign->bmi, 1),
                    'message' => 'You could benefit from gaining a little weight. A nutritionist can create a healthy plan for you.',
                    'icon' => '⚖️'
                ];
            } elseif ($vitalSign->bmi >= 30) {
                $alerts[] = [
                    'type' => 'warning',
                    'vital' => 'BMI',
                    'value' => number_format($vitalSign->bmi, 1),
                    'message' => 'Your weight could be affecting your health. Let\'s work together to create a healthy plan that works for you.',
                    'icon' => '⚖️'
                ];
            } elseif ($vitalSign->bmi >= 25) {
                $alerts[] = [
                    'type' => 'info',
                    'vital' => 'BMI',
                    'value' => number_format($vitalSign->bmi, 1),
                    'message' => 'Small lifestyle changes can help you reach a healthier weight. We\'re here to support you!',
                    'icon' => '⚖️'
                ];
            }
        }
        
        return [
            'needsAttention' => count($alerts) > 0,
            'hasCritical' => collect($alerts)->contains('type', 'critical'),
            'hasWarning' => collect($alerts)->contains('type', 'warning'),
            'alerts' => $alerts
        ];
    }
    
    /**
     * Parse blood pressure string (e.g., "120/80")
     *
     * @param string $bp
     * @return array|null
     */
    private static function parseBloodPressure(string $bp): ?array
    {
        $parts = explode('/', $bp);
        if (count($parts) === 2) {
            return [
                'systolic' => (int) trim($parts[0]),
                'diastolic' => (int) trim($parts[1])
            ];
        }
        return null;
    }
    
    /**
     * Get BMI category
     *
     * @param float $bmi
     * @return string
     */
    public static function getBMICategory(float $bmi): string
    {
        if ($bmi < 18.5) {
            return 'Underweight';
        } elseif ($bmi < 25) {
            return 'Normal Weight';
        } elseif ($bmi < 30) {
            return 'Overweight';
        } else {
            return 'Obese';
        }
    }
    
    /**
     * Get alert summary for email subject
     *
     * @param array $alertData
     * @return string
     */
    public static function getAlertSummary(array $alertData): string
    {
        if ($alertData['hasCritical']) {
            return 'Your health report - let\'s talk today';
        } elseif ($alertData['hasWarning']) {
            return 'Your health report - a check-up would be good';
        } else {
            return 'Great news! Your health report is ready';
        }
    }
}

