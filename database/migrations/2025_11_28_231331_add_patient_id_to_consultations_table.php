<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Patient;
use App\Models\Consultation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Link to Patient record
            $table->foreignId('patient_id')->nullable()->after('reference')->constrained('patients')->onDelete('set null');
            $table->index('patient_id');
        });
        
        // Auto-link existing consultations to patients by email
        $this->linkExistingConsultationsToPatients();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn('patient_id');
        });
    }
    
    /**
     * Link existing consultations to patients by email
     */
    protected function linkExistingConsultationsToPatients(): void
    {
        try {
            $consultations = Consultation::whereNull('patient_id')->get();
            
            foreach ($consultations as $consultation) {
                $patient = Patient::where('email', $consultation->email)->first();
                
                if (!$patient) {
                    // Create patient record if doesn't exist
                    $patient = Patient::create([
                        'name' => $consultation->first_name . ' ' . $consultation->last_name,
                        'email' => $consultation->email,
                        'phone' => $consultation->mobile,
                        'gender' => $consultation->gender,
                        'age' => $consultation->age,
                        'canvasser_id' => $consultation->canvasser_id,
                        'password' => bcrypt(\Illuminate\Support\Str::random(16)), // Random password
                        'is_verified' => false,
                    ]);
                }
                
                // Link consultation to patient
                $consultation->update(['patient_id' => $patient->id]);
            }
            
            \Illuminate\Support\Facades\Log::info('Linked existing consultations to patients', [
                'total_consultations' => $consultations->count()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to link consultations to patients', [
                'error' => $e->getMessage()
            ]);
        }
    }
};
