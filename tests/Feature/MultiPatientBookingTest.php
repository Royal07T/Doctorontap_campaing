<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class MultiPatientBookingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $bookingService;
    protected $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = app(BookingService::class);
        
        // Create a test doctor
        $this->doctor = Doctor::factory()->create([
            'name' => 'Dr. Test Doctor',
            'email' => 'doctor@test.com',
            'consultation_fee' => 5000,
        ]);
    }

    /** @test */
    public function it_creates_multi_patient_booking_successfully()
    {
        $bookingData = [
            'payer_name' => 'Amina Okafor',
            'payer_email' => 'amina@example.com',
            'payer_mobile' => '08012345678',
            'consult_mode' => 'video',
            'doctor_id' => $this->doctor->id,
            'patients' => [
                [
                    'first_name' => 'Amina',
                    'last_name' => 'Okafor',
                    'age' => 32,
                    'gender' => 'female',
                    'relationship' => 'self',
                    'symptoms' => 'Headache and fatigue',
                    'problem' => 'General consultation',
                    'severity' => 'moderate',
                ],
                [
                    'first_name' => 'Tunde',
                    'last_name' => 'Okafor',
                    'age' => 6,
                    'gender' => 'male',
                    'relationship' => 'child',
                    'symptoms' => 'Ear pain and fever',
                    'problem' => 'Pediatric consultation',
                    'severity' => 'moderate',
                ]
            ]
        ];

        $booking = $this->bookingService->createMultiPatientBooking($bookingData);

        // Assert booking created
        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals('Amina Okafor', $booking->payer_name);
        $this->assertEquals('amina@example.com', $booking->payer_email);
        
        // Assert patients created
        $this->assertCount(2, $booking->patients);
        
        // Assert consultations created
        $this->assertCount(2, $booking->consultations);
        
        // Assert each consultation has correct patient_id
        foreach ($booking->consultations as $consultation) {
            $this->assertNotNull($consultation->patient_id);
            $this->assertTrue($consultation->is_multi_patient_booking);
            $this->assertEquals($booking->id, $consultation->booking_id);
        }
        
        // Assert invoice created
        $this->assertNotNull($booking->invoice);
        $this->assertEquals(10000, $booking->invoice->total_amount); // 5000 x 2
        
        // Assert invoice items created
        $this->assertCount(2, $booking->invoice->items);
        
        // Assert each invoice item linked to correct patient
        foreach ($booking->invoice->items as $item) {
            $this->assertNotNull($item->patient_id);
            $this->assertNotNull($item->consultation_id);
            $this->assertEquals(5000, $item->unit_price);
        }
    }

    /** @test */
    public function it_maintains_data_integrity_between_patients()
    {
        $bookingData = [
            'payer_name' => 'Test Payer',
            'payer_email' => 'payer@test.com',
            'payer_mobile' => '08099999999',
            'consult_mode' => 'chat',
            'doctor_id' => $this->doctor->id,
            'patients' => [
                [
                    'first_name' => 'Mother',
                    'last_name' => 'Test',
                    'age' => 35,
                    'gender' => 'female',
                    'relationship' => 'self',
                    'symptoms' => 'Mother symptoms',
                    'problem' => 'Mother problem',
                    'severity' => 'moderate',
                ],
                [
                    'first_name' => 'Child',
                    'last_name' => 'Test',
                    'age' => 5,
                    'gender' => 'male',
                    'relationship' => 'child',
                    'symptoms' => 'Child symptoms',
                    'problem' => 'Child problem',
                    'severity' => 'moderate',
                ]
            ]
        ];

        $booking = $this->bookingService->createMultiPatientBooking($bookingData);

        // Get the two consultations
        $motherConsultation = $booking->consultations->where('first_name', 'Mother')->first();
        $childConsultation = $booking->consultations->where('first_name', 'Child')->first();

        // Assert they have different patient IDs
        $this->assertNotEquals($motherConsultation->patient_id, $childConsultation->patient_id);
        
        // Assert symptoms are not mixed
        $this->assertEquals('Mother symptoms', $motherConsultation->symptoms);
        $this->assertEquals('Child symptoms', $childConsultation->symptoms);
        
        // Assert each patient record is separate
        $motherPatient = Patient::find($motherConsultation->patient_id);
        $childPatient = Patient::find($childConsultation->patient_id);
        
        $this->assertNotEquals($motherPatient->id, $childPatient->id);
        $this->assertEquals(35, $motherPatient->age);
        $this->assertEquals(5, $childPatient->age);
    }

    /** @test */
    public function doctor_can_adjust_individual_patient_fees()
    {
        // Create booking
        $bookingData = [
            'payer_name' => 'Test Payer',
            'payer_email' => 'payer@test.com',
            'payer_mobile' => '08011111111',
            'consult_mode' => 'video',
            'doctor_id' => $this->doctor->id,
            'patients' => [
                [
                    'first_name' => 'Patient1',
                    'last_name' => 'Test',
                    'age' => 30,
                    'gender' => 'male',
                    'relationship' => 'self',
                    'symptoms' => 'Test',
                    'problem' => 'Test',
                    'severity' => 'moderate',
                ],
                [
                    'first_name' => 'Patient2',
                    'last_name' => 'Test',
                    'age' => 8,
                    'gender' => 'female',
                    'relationship' => 'child',
                    'symptoms' => 'Test',
                    'problem' => 'Test',
                    'severity' => 'moderate',
                ]
            ]
        ];

        $booking = $this->bookingService->createMultiPatientBooking($bookingData);
        
        // Get patient 2 (child)
        $childConsultation = $booking->consultations->where('first_name', 'Patient2')->first();
        $childPatient = Patient::find($childConsultation->patient_id);
        
        // Original total should be 10000 (5000 x 2)
        $this->assertEquals(10000, $booking->invoice->total_amount);
        
        // Adjust child's fee
        $this->bookingService->adjustPatientFee(
            $booking,
            $childPatient->id,
            3500, // New fee
            'Family discount for child',
            $this->doctor
        );
        
        // Refresh models
        $booking->refresh();
        $booking->invoice->refresh();
        
        // Assert fee was adjusted
        $adjustedItem = InvoiceItem::where('invoice_id', $booking->invoice->id)
            ->where('patient_id', $childPatient->id)
            ->first();
            
        $this->assertEquals(3500, $adjustedItem->total_price);
        $this->assertEquals(-1500, $adjustedItem->adjustment);
        
        // Assert total invoice updated
        $this->assertEquals(8500, $booking->invoice->total_amount); // 5000 + 3500
    }
}

