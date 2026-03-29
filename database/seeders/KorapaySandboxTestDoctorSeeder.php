<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorBankAccount;
use Illuminate\Database\Seeder;

/**
 * Creates (or updates) doctors with KoraPay **test mode** successful NGN bank payout details.
 *
 * @see https://developers.korapay.com/docs/testing-your-integration (Successful Payout: NGN, bank 033, account 0000000000)
 */
class KorapaySandboxTestDoctorSeeder extends Seeder
{
    public const SANDBOX_EMAIL = 'sandbox-doctor@korapay.test';

    public const ROLLA_JEHWO_EMAIL = 'stonnyrolling003@gmail.com';

    public function run(): void
    {
        $this->seedDoctorWithKorapaySandboxBank([
            'email' => self::SANDBOX_EMAIL,
            'name' => 'Dr. Sandbox KoraPay',
            'first_name' => 'Sandbox',
            'last_name' => 'KoraPay',
            'phone' => '08000000000',
            'password' => 'SandboxDoctor123!',
            'gender' => 'Male',
            'specialization' => 'General Practitioner',
            'bio' => 'Test-only doctor for KoraPay sandbox successful bank payouts (NGN).',
            'bank_account_name' => 'Sandbox Test Doctor',
        ]);

        $this->seedDoctorWithKorapaySandboxBank([
            'email' => self::ROLLA_JEHWO_EMAIL,
            'name' => 'Dr. Rolla Jehwo',
            'first_name' => 'Rolla',
            'last_name' => 'Jehwo',
            'phone' => '08000000000',
            'password' => 'password',
            'gender' => 'Male',
            'specialization' => 'Dermatologist',
            'bio' => 'KoraPay sandbox successful bank payouts (NGN): UBA 033 / 0000000000.',
            'bank_account_name' => 'Dr. Rolla Jehwo',
        ]);
    }

    /**
     * @param  array{
     *     email: string,
     *     name: string,
     *     first_name: string,
     *     last_name: string,
     *     password: string,
     *     specialization: string,
     *     bio?: string,
     *     phone?: string,
     *     gender?: string,
     *     bank_account_name?: string
     * }  $profile
     */
    private function seedDoctorWithKorapaySandboxBank(array $profile): Doctor
    {
        $email = $profile['email'];
        $bankAccountName = $profile['bank_account_name'] ?? $profile['name'];
        unset($profile['email'], $profile['bank_account_name']);

        $doctor = Doctor::query()->updateOrCreate(
            ['email' => $email],
            array_merge([
                'consultation_fee' => 5000,
                'use_default_fee' => false,
                'location' => 'Lagos',
                'experience' => '1 year',
                'languages' => 'English',
                'is_available' => true,
                'is_approved' => true,
                'approved_at' => now(),
                'email_verified_at' => now(),
            ], $profile)
        );

        $prior = DoctorBankAccount::withTrashed()
            ->where('doctor_id', $doctor->id)
            ->where('bank_code', '033')
            ->where('account_number', '0000000000')
            ->first();

        if ($prior?->trashed()) {
            $prior->restore();
        }

        DoctorBankAccount::where('doctor_id', $doctor->id)->update(['is_default' => false]);

        $account = DoctorBankAccount::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'bank_code' => '033',
                'account_number' => '0000000000',
            ],
            [
                'bank_name' => 'United Bank for Africa',
                'account_name' => $bankAccountName,
                'account_type' => 'current',
                'is_verified' => true,
                'verified_at' => now(),
                'is_default' => true,
                'notes' => 'KoraPay sandbox SUCCESS (NGN): bank code 033, account 0000000000 — developers.korapay.com/docs/testing-your-integration',
            ]
        );

        $account->setAsDefault();

        $this->command?->info('KoraPay sandbox bank ready:');
        $this->command?->line('  Email:     '.$email);
        $this->command?->line('  Bank:      UBA (033) / 0000000000 — verified, default');
        $this->command?->line('  Doctor ID: '.$doctor->id);
        $this->command?->newLine();

        return $doctor;
    }
}
