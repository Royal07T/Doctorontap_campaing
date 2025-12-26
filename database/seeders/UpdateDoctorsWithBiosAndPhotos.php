<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UpdateDoctorsWithBiosAndPhotos extends Seeder
{
    /**
     * Generate a professional bio based on specialty
     */
    private function generateBio($specialty, $firstName, $experience, $location, $faker)
    {
        // Extract years from experience string
        preg_match('/(\d+)/', $experience, $matches);
        $experienceYears = isset($matches[1]) ? (int)$matches[1] : 5;
        
        $specialtyLower = strtolower($specialty);
        
        $bioTemplates = [
            'cardiology' => "Dr. {$firstName} is a dedicated {$specialtyLower} with {$experienceYears} years of experience in diagnosing and treating cardiovascular conditions. Based in {$location}, Dr. {$firstName} specializes in preventive cardiology, heart disease management, and cardiac rehabilitation. Committed to providing compassionate care and helping patients achieve optimal heart health.",
            
            'pediatrics' => "Dr. {$firstName} is a compassionate {$specialtyLower} with {$experienceYears} years of experience caring for children from infancy through adolescence. Located in {$location}, Dr. {$firstName} focuses on preventive care, developmental milestones, and treating childhood illnesses. Known for creating a warm, child-friendly environment that puts both children and parents at ease.",
            
            'obstetrics' => "Dr. {$firstName} is an experienced {$specialtyLower} with {$experienceYears} years of expertise in women's reproductive health, pregnancy care, and gynecological conditions. Practicing in {$location}, Dr. {$firstName} provides comprehensive care throughout all stages of a woman's life, from adolescence through menopause.",
            
            'gynecology' => "Dr. {$firstName} is an experienced {$specialtyLower} with {$experienceYears} years of expertise in women's reproductive health, pregnancy care, and gynecological conditions. Practicing in {$location}, Dr. {$firstName} provides comprehensive care throughout all stages of a woman's life, from adolescence through menopause.",
            
            'neurology' => "Dr. {$firstName} is a skilled {$specialtyLower} with {$experienceYears} years of experience in diagnosing and treating disorders of the nervous system. Based in {$location}, Dr. {$firstName} specializes in headaches, epilepsy, stroke management, and neurodegenerative conditions. Committed to improving patients' quality of life through advanced neurological care.",
            
            'psychiatry' => "Dr. {$firstName} is a compassionate {$specialtyLower} with {$experienceYears} years of experience in mental health diagnosis and treatment. Located in {$location}, Dr. {$firstName} provides comprehensive psychiatric care, medication management, and therapy for various mental health conditions. Dedicated to supporting patients on their journey to mental wellness.",
            
            'therapist' => "Dr. {$firstName} is a compassionate {$specialtyLower} with {$experienceYears} years of experience in mental health counseling and psychotherapy. Located in {$location}, Dr. {$firstName} provides supportive therapy, helping patients navigate life's challenges and improve their mental well-being. Known for creating a safe, non-judgmental therapeutic environment.",
            
            'dermatology' => "Dr. {$firstName} is an expert {$specialtyLower} with {$experienceYears} years of experience in treating skin, hair, and nail conditions. Practicing in {$location}, Dr. {$firstName} specializes in medical dermatology, cosmetic procedures, and skin cancer prevention. Known for providing personalized treatment plans tailored to each patient's needs.",
            
            'orthopaedics' => "Dr. {$firstName} is a specialized {$specialtyLower} with {$experienceYears} years of experience in treating bone, joint, and musculoskeletal conditions. Based in {$location}, Dr. {$firstName} focuses on sports injuries, joint replacement, and orthopedic surgery. Committed to helping patients regain mobility and return to their active lifestyles.",
            
            'orthopedic' => "Dr. {$firstName} is a specialized {$specialtyLower} with {$experienceYears} years of experience in treating bone, joint, and musculoskeletal conditions. Based in {$location}, Dr. {$firstName} focuses on sports injuries, joint replacement, and orthopedic surgery. Committed to helping patients regain mobility and return to their active lifestyles.",
            
            'oncology' => "Dr. {$firstName} is a dedicated {$specialtyLower} with {$experienceYears} years of experience in cancer diagnosis and treatment. Located in {$location}, Dr. {$firstName} provides compassionate, comprehensive cancer care, working closely with patients and their families throughout their treatment journey. Specializes in personalized treatment plans and supportive care.",
            
            'ophthalmology' => "Dr. {$firstName} is an experienced {$specialtyLower} with {$experienceYears} years of expertise in eye care and vision treatment. Practicing in {$location}, Dr. {$firstName} specializes in cataract surgery, glaucoma management, diabetic eye care, and general ophthalmology. Committed to preserving and improving patients' vision.",
            
            'gastroenterology' => "Dr. {$firstName} is a skilled {$specialtyLower} with {$experienceYears} years of experience in diagnosing and treating digestive system disorders. Based in {$location}, Dr. {$firstName} specializes in gastrointestinal conditions, liver diseases, and endoscopic procedures. Known for providing thorough evaluations and effective treatment plans.",
            
            'general practice' => "Dr. {$firstName} is a dedicated {$specialtyLower} with {$experienceYears} years of experience providing comprehensive primary care. Based in {$location}, Dr. {$firstName} offers family medicine services, preventive care, and treatment for a wide range of health conditions. Known for building long-term relationships with patients and providing personalized, compassionate care.",
            
            'internal medicine' => "Dr. {$firstName} is an experienced {$specialtyLower} with {$experienceYears} years of expertise in diagnosing and treating adult diseases and complex medical conditions. Located in {$location}, Dr. {$firstName} provides comprehensive internal medicine care, focusing on preventive health and management of chronic conditions.",
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
        
        $this->command->info('Updating existing doctors with bios and profile pictures...');
        $this->command->newLine();

        // Get all doctors to update bios and photos
        $doctors = Doctor::all();

        $updatedCount = 0;
        $totalDoctors = $doctors->count();

        foreach ($doctors as $doctor) {
            $firstName = $doctor->first_name ?: explode(' ', $doctor->name)[1] ?? 'Doctor';
            $lastName = $doctor->last_name ?: explode(' ', $doctor->name)[count(explode(' ', $doctor->name)) - 1] ?? 'Name';
            
            // Remove "Dr." prefix if present
            $firstName = str_replace('Dr.', '', $firstName);
            $lastName = str_replace('Dr.', '', $lastName);
            $firstName = trim($firstName);
            $lastName = trim($lastName);
            
            // If still empty, use name field
            if (empty($firstName) || empty($lastName)) {
                $nameParts = explode(' ', $doctor->name);
                $firstName = $nameParts[1] ?? 'Doctor';
                $lastName = $nameParts[count($nameParts) - 1] ?? 'Name';
            }

            $updates = [];

            // Always update bio with professional one
            $bio = $this->generateBio(
                $doctor->specialization ?? 'General Practice',
                $firstName,
                $doctor->experience ?? '5 years',
                $doctor->location ?? 'Nigeria',
                $faker
            );
            $updates['bio'] = $bio;

            // Add photo if missing, or update if it's a placeholder
            if (empty($doctor->photo) || !Storage::disk('public')->exists($doctor->photo)) {
                $photoPath = $this->downloadProfilePicture($firstName, $lastName, $doctor->gender ?? 'male');
                if ($photoPath) {
                    // Delete old photo if exists
                    if ($doctor->photo && Storage::disk('public')->exists($doctor->photo)) {
                        Storage::disk('public')->delete($doctor->photo);
                    }
                    $updates['photo'] = $photoPath;
                }
            }

            if (!empty($updates)) {
                $doctor->update($updates);
                $updatedCount++;
                
                // Progress indicator
                if ($updatedCount % 10 === 0) {
                    $this->command->info("Updated {$updatedCount}/{$totalDoctors} doctors...");
                }
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Successfully updated {$updatedCount} doctors!");
        $this->command->newLine();
        $this->command->info('📋 Summary:');
        $this->command->info("   • Doctors updated: {$updatedCount}");
        $this->command->info("   • Professional bios added: ✅");
        $this->command->info("   • Profile pictures added: ✅");
    }
}

