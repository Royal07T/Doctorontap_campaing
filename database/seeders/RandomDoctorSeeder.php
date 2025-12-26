<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class RandomDoctorSeeder extends Seeder
{
    /**
     * List of medical specialties
     */
    private $specialties = [
        'General Practice (Family Medicine)',
        'Internal Medicine',
        'Pediatrics',
        'Obstetrics & Gynecology (OB/GYN)',
        'Cardiology',
        'Neurology',
        'Psychiatry',
        'Therapist',
        'Dermatology',
        'Oncology',
        'Geriatrics',
        'Ophthalmology',
        'ENT (Otolaryngology)',
        'Endocrinology',
        'Nephrology',
        'Gastroenterology',
        'Urology',
        'Orthopaedics',
        'General Practitioner (GP)',
        'Cardiologist',
        'Endocrinologist',
        'Gastroenterologist',
        'Neurologist',
        'Psychiatrist',
        'Orthopedic Specialist',
        'Dermatologist',
        'Pulmonologist',
        'Nephrologist',
        'Urologist',
        'Oncologist',
        'Ophthalmologist',
        'ENT Specialist (Otolaryngologist)',
        'Emergency Medicine Physician',
        'Pain Management Specialist',
        'Sports Medicine Physician',
        'Geriatrician',
    ];

    /**
     * Nigerian cities
     */
    private $cities = [
        'Lagos', 'Abuja', 'Kano', 'Ibadan', 'Port Harcourt', 
        'Benin City', 'Kaduna', 'Aba', 'Maiduguri', 'Ilorin',
        'Onitsha', 'Warri', 'Enugu', 'Calabar', 'Uyo',
        'Jos', 'Abeokuta', 'Akure', 'Owerri', 'Sokoto'
    ];

    /**
     * Languages commonly spoken in Nigeria
     */
    private $languages = [
        'English',
        'English, Yoruba',
        'English, Hausa',
        'English, Igbo',
        'English, Pidgin',
        'English, Yoruba, Hausa',
        'English, Igbo, Pidgin',
        'English, Hausa, Fulfulde',
    ];

    /**
     * Places of work
     */
    private $placesOfWork = [
        'Lagos University Teaching Hospital',
        'National Hospital Abuja',
        'Ahmadu Bello University Teaching Hospital',
        'University College Hospital Ibadan',
        'Federal Medical Centre',
        'Private Practice',
        'General Hospital',
        'St. Mary\'s Hospital',
        'Mercy Medical Center',
        'Hope Clinic',
        'Life Care Hospital',
        'Community Health Center',
    ];

    /**
     * Generate a professional bio based on specialty
     */
    private function generateBio($specialty, $firstName, $experienceYears, $location, $faker)
    {
        $specialtyLower = strtolower($specialty);
        
        $bioTemplates = [
            'cardiology' => "Dr. {$firstName} is a dedicated {$specialtyLower} with {$experienceYears} years of experience in diagnosing and treating cardiovascular conditions. Based in {$location}, Dr. {$firstName} specializes in preventive cardiology, heart disease management, and cardiac rehabilitation. Committed to providing compassionate care and helping patients achieve optimal heart health.",
            
            'pediatrics' => "Dr. {$firstName} is a compassionate {$specialtyLower} with {$experienceYears} years of experience caring for children from infancy through adolescence. Located in {$location}, Dr. {$firstName} focuses on preventive care, developmental milestones, and treating childhood illnesses. Known for creating a warm, child-friendly environment that puts both children and parents at ease.",
            
            'obstetrics' => "Dr. {$firstName} is an experienced {$specialtyLower} with {$experienceYears} years of expertise in women's reproductive health, pregnancy care, and gynecological conditions. Practicing in {$location}, Dr. {$firstName} provides comprehensive care throughout all stages of a woman's life, from adolescence through menopause.",
            
            'neurology' => "Dr. {$firstName} is a skilled {$specialtyLower} with {$experienceYears} years of experience in diagnosing and treating disorders of the nervous system. Based in {$location}, Dr. {$firstName} specializes in headaches, epilepsy, stroke management, and neurodegenerative conditions. Committed to improving patients' quality of life through advanced neurological care.",
            
            'psychiatry' => "Dr. {$firstName} is a compassionate {$specialtyLower} with {$experienceYears} years of experience in mental health diagnosis and treatment. Located in {$location}, Dr. {$firstName} provides comprehensive psychiatric care, medication management, and therapy for various mental health conditions. Dedicated to supporting patients on their journey to mental wellness.",
            
            'dermatology' => "Dr. {$firstName} is an expert {$specialtyLower} with {$experienceYears} years of experience in treating skin, hair, and nail conditions. Practicing in {$location}, Dr. {$firstName} specializes in medical dermatology, cosmetic procedures, and skin cancer prevention. Known for providing personalized treatment plans tailored to each patient's needs.",
            
            'orthopaedics' => "Dr. {$firstName} is a specialized {$specialtyLower} with {$experienceYears} years of experience in treating bone, joint, and musculoskeletal conditions. Based in {$location}, Dr. {$firstName} focuses on sports injuries, joint replacement, and orthopedic surgery. Committed to helping patients regain mobility and return to their active lifestyles.",
            
            'oncology' => "Dr. {$firstName} is a dedicated {$specialtyLower} with {$experienceYears} years of experience in cancer diagnosis and treatment. Located in {$location}, Dr. {$firstName} provides compassionate, comprehensive cancer care, working closely with patients and their families throughout their treatment journey. Specializes in personalized treatment plans and supportive care.",
            
            'ophthalmology' => "Dr. {$firstName} is an experienced {$specialtyLower} with {$experienceYears} years of expertise in eye care and vision treatment. Practicing in {$location}, Dr. {$firstName} specializes in cataract surgery, glaucoma management, diabetic eye care, and general ophthalmology. Committed to preserving and improving patients' vision.",
            
            'gastroenterology' => "Dr. {$firstName} is a skilled {$specialtyLower} with {$experienceYears} years of experience in diagnosing and treating digestive system disorders. Based in {$location}, Dr. {$firstName} specializes in gastrointestinal conditions, liver diseases, and endoscopic procedures. Known for providing thorough evaluations and effective treatment plans.",
        ];

        // Check for specialty keywords
        foreach ($bioTemplates as $key => $template) {
            if (stripos($specialtyLower, $key) !== false) {
                return $template;
            }
        }

        // Default bio template
        return "Dr. {$firstName} is a dedicated {$specialtyLower} with {$experienceYears} years of experience providing high-quality medical care. Based in {$location}, Dr. {$firstName} is committed to patient-centered care, staying current with the latest medical advances, and building long-term relationships with patients. Known for thorough evaluations, clear communication, and compassionate treatment approaches.";
    }

    /**
     * Download and store profile picture
     */
    private function downloadProfilePicture($firstName, $lastName, $gender)
    {
        try {
            // Use UI Avatars API to generate professional avatars
            $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
            $genderColor = $gender === 'male' ? '4F46E5' : 'EC4899'; // Blue for male, Pink for female
            
            // Generate avatar URL
            $avatarUrl = "https://ui-avatars.com/api/?name={$initials}&size=400&background={$genderColor}&color=ffffff&bold=true&font-size=0.5";
            
            // Download the image
            $response = Http::timeout(10)->get($avatarUrl);
            
            if ($response->successful()) {
                // Create doctors directory if it doesn't exist
                if (!Storage::disk('public')->exists('doctors')) {
                    Storage::disk('public')->makeDirectory('doctors');
                }
                
                // Generate filename
                $fileName = Str::slug("{$firstName}-{$lastName}") . '-' . time() . '.png';
                $path = 'doctors/' . $fileName;
                
                // Store the image
                Storage::disk('public')->put($path, $response->body());
                
                return $path;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to download profile picture', [
                'doctor' => "{$firstName} {$lastName}",
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        
        $this->command->info('Creating random doctors with different specialties...');
        $this->command->newLine();

        $createdCount = 0;
        $specialtyCount = count($this->specialties);
        
        // Create 2-3 doctors per specialty to ensure variety
        $doctorsPerSpecialty = 2;
        $totalDoctors = $specialtyCount * $doctorsPerSpecialty;

        foreach ($this->specialties as $index => $specialty) {
            for ($i = 0; $i < $doctorsPerSpecialty; $i++) {
                $gender = $faker->randomElement(['male', 'female']);
                $firstName = $gender === 'male' 
                    ? $faker->firstNameMale() 
                    : $faker->firstNameFemale();
                $lastName = $faker->lastName();
                $fullName = "Dr. {$firstName} {$lastName}";
                
                // Generate unique email
                $email = strtolower("{$firstName}.{$lastName}." . ($i + 1) . "@doctorontap.test");
                $email = str_replace(' ', '', $email);
                
                // Generate Nigerian phone number
                $phone = '0' . $faker->numberBetween(700, 999) . ' ' . 
                         $faker->numberBetween(100, 999) . ' ' . 
                         $faker->numberBetween(1000, 9999);

                // Generate experience (2-25 years)
                $experienceYears = $faker->numberBetween(2, 25);
                $experience = $experienceYears . ' year' . ($experienceYears > 1 ? 's' : '');

                // Generate consultation fee (3000 - 15000)
                $consultationFee = $faker->numberBetween(3000, 15000);
                $minFee = $consultationFee - $faker->numberBetween(500, 2000);
                $maxFee = $consultationFee + $faker->numberBetween(500, 2000);

                $location = $faker->randomElement($this->cities);
                
                // Generate professional bio
                $bio = $this->generateBio($specialty, $firstName, $experienceYears, $location, $faker);
                
                // Download profile picture
                $photoPath = $this->downloadProfilePicture($firstName, $lastName, $gender);

                // Create doctor
                $doctor = Doctor::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => $fullName,
                    'email' => $email,
                    'password' => Hash::make('password'), // Password is "password"
                    'phone' => $phone,
                    'gender' => $gender,
                    'specialization' => $specialty,
                    'consultation_fee' => $consultationFee,
                    'min_consultation_fee' => $minFee,
                    'max_consultation_fee' => $maxFee,
                    'use_default_fee' => false,
                    'location' => $location,
                    'experience' => $experience,
                    'languages' => $faker->randomElement($this->languages),
                    'place_of_work' => $faker->randomElement($this->placesOfWork),
                    'role' => 'clinical',
                    'mdcn_license_current' => $faker->boolean(80), // 80% have current license
                    'bio' => $bio,
                    'photo' => $photoPath,
                    'is_available' => $faker->boolean(90), // 90% are available
                    'is_approved' => true, // All approved
                    'approved_by' => 1, // Assuming admin user ID 1 exists
                    'approved_at' => now(),
                    'email_verified_at' => now(), // Email verified
                    'order' => ($index * $doctorsPerSpecialty) + $i + 1,
                ]);

                $createdCount++;
                
                // Progress indicator
                if ($createdCount % 10 === 0) {
                    $this->command->info("Created {$createdCount}/{$totalDoctors} doctors...");
                }
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Successfully created {$createdCount} random doctors!");
        $this->command->newLine();
        $this->command->info('📋 Summary:');
        $this->command->info("   • Total doctors: {$createdCount}");
        $this->command->info("   • Specialties covered: {$specialtyCount}");
        $this->command->info("   • All emails verified: ✅");
        $this->command->info("   • All passwords: 'password'");
        $this->command->info("   • Professional bios added: ✅");
        $this->command->info("   • Profile pictures added: ✅");
        $this->command->newLine();
        $this->command->info('🔑 Login credentials:');
        $this->command->info('   Email: [any doctor email from the list above]');
        $this->command->info('   Password: password');
        $this->command->newLine();
        $this->command->info('💡 Tip: Check the doctors table or admin panel to see all created doctors.');
    }
}

