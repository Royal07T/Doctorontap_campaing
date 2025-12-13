<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            [
                'id' => 1,
                'name' => 'General Practice (Family Medicine)',
                'slug' => 'general-practice-family-medicine',
                'description' => 'Comprehensive primary care for individuals and families of all ages',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'Internal Medicine',
                'slug' => 'internal-medicine',
                'description' => 'Diagnosis and treatment of adult diseases and conditions',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Pediatrics',
                'slug' => 'pediatrics',
                'description' => 'Medical care for infants, children, and adolescents',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Obstetrics & Gynecology (OB/GYN)',
                'slug' => 'obstetrics-gynecology-obgyn',
                'description' => 'Women\'s reproductive health, pregnancy, and childbirth',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'description' => 'Diagnosis and treatment of heart and cardiovascular conditions',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'name' => 'Neurology',
                'slug' => 'neurology',
                'description' => 'Treatment of disorders of the nervous system',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 7,
                'name' => 'Psychiatry',
                'slug' => 'psychiatry',
                'description' => 'Mental health diagnosis, treatment, and therapy',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 8,
                'name' => 'Therapist',
                'slug' => 'therapist',
                'description' => 'Mental health counseling and psychotherapy services',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 9,
                'name' => 'Dermatology',
                'slug' => 'dermatology',
                'description' => 'Diagnosis and treatment of skin conditions and diseases',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 10,
                'name' => 'Oncology',
                'slug' => 'oncology',
                'description' => 'Diagnosis and treatment of cancer',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 11,
                'name' => 'Geriatrics',
                'slug' => 'geriatrics',
                'description' => 'Medical care for elderly patients',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 12,
                'name' => 'Ophthalmology',
                'slug' => 'ophthalmology',
                'description' => 'Eye care and vision treatment',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 13,
                'name' => 'ENT (Otolaryngology)',
                'slug' => 'ent-otolaryngology',
                'description' => 'Ear, nose, and throat disorders',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 14,
                'name' => 'Endocrinology',
                'slug' => 'endocrinology',
                'description' => 'Hormone and metabolic disorders',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 15,
                'name' => 'Nephrology',
                'slug' => 'nephrology',
                'description' => 'Kidney disease and renal conditions',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 16,
                'name' => 'Gastroenterology',
                'slug' => 'gastroenterology',
                'description' => 'Digestive system disorders',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 17,
                'name' => 'Urology',
                'slug' => 'urology',
                'description' => 'Urinary tract and male reproductive system',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
            [
                'id' => 18,
                'name' => 'Orthopaedics',
                'slug' => 'orthopaedics',
                'description' => 'Bone, joint, and musculoskeletal conditions',
                'is_active' => true,
                'created_at' => '2025-08-20 06:56:35',
                'updated_at' => '2025-08-20 06:56:35',
                'deleted_at' => null,
            ],
        ];

        foreach ($specialties as $specialty) {
            DB::table('specialties')->updateOrInsert(
                ['id' => $specialty['id']],
                $specialty
            );
        }
    }
}
