<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Consultation;
use App\Models\Doctor;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentRequest;

class ConsultationTable extends Component
{
    use WithPagination;
    
    // Public properties are automatically bound to the view and synced with frontend
    public $search = '';
    public $status = '';
    public $payment_status = '';
    
    // Reset pagination when filters change
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatus()
    {
        $this->resetPage();
    }
    
    public function updatingPaymentStatus()
    {
        $this->resetPage();
    }
    
    /**
     * Update consultation status
     */
    public function updateStatus($consultationId, $newStatus)
    {
        try {
            $consultation = Consultation::findOrFail($consultationId);
            $oldStatus = $consultation->status;
            
            $consultation->status = $newStatus;
            $consultation->save();
            
            // Send notification if needed (non-blocking)
            if ($oldStatus !== $newStatus && $consultation->doctor) {
                try {
                    $adminEmail = config('mail.admin_email');
                    if ($adminEmail) {
                        Mail::to($adminEmail)
                            ->send(new \App\Mail\ConsultationStatusChange(
                                $consultation,
                                $consultation->doctor,
                                $oldStatus,
                                $newStatus
                            ));
                    }
                } catch (\Exception $emailException) {
                    // Log email error but don't fail the status update
                    \Log::warning('Failed to send consultation status change notification email', [
                        'consultation_id' => $consultation->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'error' => $emailException->getMessage()
                    ]);
                }
            }
            
            $this->dispatch('alert', 
                message: 'Status updated successfully!',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to update status: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }
    
    /**
     * Send payment request
     */
    public function sendPaymentRequest($consultationId)
    {
        try {
            $consultation = Consultation::findOrFail($consultationId);
            
            if ($consultation->status !== 'completed') {
                throw new \Exception('Consultation must be completed before sending payment request');
            }
            
            // Send payment request email
            Mail::to($consultation->email)->send(new PaymentRequest($consultation));
            
            // Update payment request status
            $consultation->payment_request_sent = true;
            $consultation->payment_request_sent_at = now();
            $consultation->save();
            
            $message = $consultation->payment_request_sent ? 
                'Payment request resent successfully!' : 
                'Payment request sent successfully!';
            
            $this->dispatch('alert', 
                message: $message,
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to send payment request: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }
    
    /**
     * Reassign doctor to consultation
     */
    public function reassignDoctor($consultationId, $doctorId)
    {
        try {
            $consultation = Consultation::findOrFail($consultationId);
            $oldDoctor = $consultation->doctor;
            $newDoctor = Doctor::findOrFail($doctorId);
            
            $consultation->doctor_id = $doctorId;
            $consultation->save();
            
            // Send notification to new doctor
            if ($newDoctor) {
                Mail::to($newDoctor->email)
                    ->send(new \App\Mail\ConsultationDoctorNotification($consultation, $newDoctor));
            }
            
            $this->dispatch('alert', 
                message: 'Doctor reassigned successfully!',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to reassign doctor: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }
    
    /**
     * Delete consultation
     */
    public function deleteConsultation($consultationId)
    {
        try {
            $consultation = Consultation::findOrFail($consultationId);
            $consultation->delete();
            
            $this->dispatch('alert', 
                message: 'Consultation deleted successfully!',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to delete consultation: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        $consultations = Consultation::query()
            ->with(['doctor', 'payment', 'canvasser', 'nurse'])
            ->when($this->search, function($query) {
                $search = $this->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('reference', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    
                    // If search contains a space, try searching first and last name separately
                    if (strpos($search, ' ') !== false) {
                        $parts = explode(' ', trim($search), 2);
                        if (count($parts) == 2) {
                            $q->orWhere(function($subQ) use ($parts) {
                                $subQ->where('first_name', 'like', "%{$parts[0]}%")
                                     ->where('last_name', 'like', "%{$parts[1]}%");
                            });
                        }
                    }
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->payment_status, fn($q) => $q->where('payment_status', $this->payment_status))
            ->latest()
            ->paginate(15);
            
        $doctors = Doctor::where('is_active', true)->get();
        
        return view('livewire.admin.consultation-table', [
            'consultations' => $consultations,
            'doctors' => $doctors
        ]);
    }
}
