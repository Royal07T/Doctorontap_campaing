<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Models\Review;
use App\Models\Patient;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DoctorConsultationsAndReviewsSeeder extends Seeder
{
    /**
     * Review comments templates
     */
    private $reviewComments = [
        'Excellent doctor! Very professional and caring.',
        'Great consultation experience. The doctor was thorough and explained everything clearly.',
        'Highly recommend! Very knowledgeable and patient.',
        'Wonderful doctor. Made me feel comfortable and addressed all my concerns.',
        'Professional and compassionate. Would definitely book again.',
        'Very satisfied with the consultation. The doctor was attentive and helpful.',
        'Great experience overall. The doctor provided clear guidance and treatment plan.',
        'Excellent service! The doctor was professional and understanding.',
        'Very good consultation. The doctor listened carefully and provided helpful advice.',
        'Outstanding doctor! Very thorough and caring approach.',
        'Highly professional. The doctor explained everything in detail.',
        'Great doctor! Very knowledgeable and made me feel at ease.',
        'Excellent consultation. The doctor was patient and answered all my questions.',
        'Very satisfied. The doctor provided comprehensive care and follow-up.',
        'Professional and friendly. Would highly recommend this doctor.',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        
        $this->command->info('Creating consultations and reviews for doctors...');
        $this->command->newLine();

        $doctors = Doctor::where('is_approved', true)->get();
        $totalDoctors = $doctors->count();
        $processedCount = 0;

        foreach ($doctors as $doctor) {
            // Determine number of consultations (more popular doctors get more)
            // Some doctors will have 0-5, some 5-15, some 15-30, some 30-50
            $consultationCount = $faker->numberBetween(0, 50);
            
            // Create consultations for this doctor
            for ($i = 0; $i < $consultationCount; $i++) {
                // Create or get a patient
                $patient = Patient::inRandomOrder()->first();
                
                if (!$patient) {
                    // Create a random patient if none exists
                    $patient = Patient::create([
                        'name' => $faker->name(),
                        'email' => $faker->unique()->safeEmail(),
                        'phone' => '0' . $faker->numberBetween(700, 999) . ' ' . 
                                  $faker->numberBetween(100, 999) . ' ' . 
                                  $faker->numberBetween(1000, 9999),
                        'gender' => $faker->randomElement(['male', 'female']),
                    ]);
                }

                // Generate consultation date (within last 6 months)
                $consultationDate = $faker->dateTimeBetween('-6 months', 'now');
                
                // Create consultation
                $consultation = Consultation::create([
                    'reference' => 'CONS-' . strtoupper(Str::random(8)),
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'first_name' => $patient->name ? explode(' ', $patient->name)[0] : $faker->firstName(),
                    'last_name' => $patient->name ? (count(explode(' ', $patient->name)) > 1 ? explode(' ', $patient->name)[1] : '') : $faker->lastName(),
                    'email' => $patient->email,
                    'mobile' => $patient->phone,
                    'age' => $faker->numberBetween(18, 80),
                    'gender' => $patient->gender ?? $faker->randomElement(['male', 'female']),
                    'problem' => $faker->sentence(10),
                    'severity' => $faker->randomElement(['mild', 'moderate', 'severe']),
                    'consult_mode' => $faker->randomElement(['voice', 'video', 'chat']),
                    'status' => $faker->randomElement(['completed', 'completed', 'completed', 'scheduled', 'in_progress']), // More completed
                    'payment_status' => $faker->randomElement(['paid', 'paid', 'paid', 'pending', 'unpaid']), // More paid
                    'consultation_completed_at' => $consultationDate,
                    'treatment_plan_created' => $faker->boolean(70), // 70% have treatment plans
                    'treatment_plan_created_at' => $faker->boolean(70) ? $consultationDate : null,
                    'created_at' => $consultationDate,
                    'updated_at' => $consultationDate,
                ]);

                // Create review for completed consultations (80% of completed ones)
                if ($consultation->status === 'completed' && $faker->boolean(80)) {
                    $rating = $faker->randomElement([5, 5, 5, 4, 4, 4, 3, 2, 1]); // More 4-5 star ratings
                    
                    Review::create([
                        'consultation_id' => $consultation->id,
                        'reviewer_type' => 'patient',
                        'patient_id' => $patient->id,
                        'reviewee_type' => 'doctor',
                        'reviewee_doctor_id' => $doctor->id,
                        'rating' => $rating,
                        'comment' => $faker->randomElement($this->reviewComments),
                        'would_recommend' => $rating >= 4,
                        'is_published' => true,
                        'is_verified' => $faker->boolean(60), // 60% verified
                        'verified_at' => $faker->boolean(60) ? $consultationDate : null,
                        'created_at' => $consultationDate,
                        'updated_at' => $consultationDate,
                    ]);
                }
            }

            $processedCount++;
            
            // Progress indicator
            if ($processedCount % 10 === 0) {
                $this->command->info("Processed {$processedCount}/{$totalDoctors} doctors...");
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Successfully created consultations and reviews!");
        $this->command->newLine();
        
        // Show statistics
        $totalConsultations = Consultation::count();
        $totalReviews = Review::count();
        $avgRating = Review::where('reviewee_type', 'doctor')->avg('rating');
        
        $this->command->info('📊 Statistics:');
        $this->command->info("   • Total consultations: {$totalConsultations}");
        $this->command->info("   • Total reviews: {$totalReviews}");
        $this->command->info("   • Average rating: " . number_format($avgRating, 2) . " stars");
        $this->command->newLine();
        
        // Show top doctors
        $topDoctors = Doctor::withCount('consultations')
            ->withCount(['reviews as published_reviews_count' => function($query) {
                $query->where('is_published', true);
            }])
            ->orderBy('consultations_count', 'desc')
            ->take(5)
            ->get();
            
        $this->command->info('🏆 Top 5 Most Consulted Doctors:');
        foreach ($topDoctors as $index => $doctor) {
            $avgRating = $doctor->reviews()->where('is_published', true)->avg('rating') ?? 0;
            $this->command->info("   " . ($index + 1) . ". {$doctor->name} - {$doctor->consultations_count} consultations, " . 
                                number_format($avgRating, 1) . "⭐ ({$doctor->published_reviews_count} reviews)");
        }
    }
}

