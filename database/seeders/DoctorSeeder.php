<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            [
                'name' => 'Dr. Hafsat Abdullahi Bashir',
                'phone' => '0813 927 8444',
                'email' => 'Hafsatbasheer@gmail.com',
                'gender' => 'Female',
                'specialization' => 'General Practitioner',
                'consultation_fee' => 3000,
                'location' => 'Kano',
                'experience' => '4 years',
                'languages' => 'English, Hausa and Arabic',
                'is_available' => true,
                'order' => 1,
            ],
            [
                'name' => 'Dr. Isah Iliyasu',
                'phone' => '08167515870',
                'email' => 'Safapps2016@gmail.com',
                'gender' => 'Male',
                'specialization' => 'General Practitioner',
                'consultation_fee' => 3000,
                'location' => 'Kano',
                'experience' => '4 years',
                'languages' => 'English and Hausa',
                'is_available' => true,
                'order' => 2,
            ],
            [
                'name' => 'Dr. Akintola Emmanuel',
                'phone' => '08132192035',
                'email' => 'Emmanuelakintola9@gmail.com',
                'gender' => 'Male',
                'specialization' => 'General Practitioner',
                'consultation_fee' => 3000,
                'location' => 'Uyo',
                'experience' => '2 years',
                'languages' => 'English',
                'is_available' => true,
                'order' => 3,
            ],
            [
                'name' => 'Dr. Dapoet Naanep',
                'phone' => '09125940375',
                'email' => 'Dapoetnaanep@gmail.com',
                'gender' => 'Female',
                'specialization' => 'General Practitioner',
                'consultation_fee' => 3000,
                'location' => 'Abuja',
                'experience' => '2 years',
                'languages' => 'English and Hausa',
                'is_available' => true,
                'order' => 4,
            ],
            [
                'name' => 'Dr. Princess Chris',
                'phone' => '08106281334',
                'email' => 'krisprincess28@gmail.com',
                'gender' => 'Female',
                'specialization' => 'General Practitioner',
                'consultation_fee' => 3000,
                'location' => 'Abuja',
                'experience' => '2 years',
                'languages' => 'English',
                'is_available' => true,
                'order' => 5,
            ],
            [
                'name' => 'Dr. Chinenye Agu',
                'phone' => '09033804848',
                'email' => 'agudaphne@gmail.com',
                'gender' => 'Female',
                'specialization' => 'General Practitioner',
                'consultation_fee' => 3000,
                'location' => 'Lagos',
                'experience' => '2 years',
                'languages' => 'English',
                'is_available' => true,
                'order' => 6,
            ],
        ];

        foreach ($doctors as $doctor) {
            Doctor::create($doctor);
        }
    }
}
