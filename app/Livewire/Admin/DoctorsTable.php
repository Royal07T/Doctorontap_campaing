<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Doctor;
use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignNotification;

class DoctorsTable extends Component
{
    use WithPagination;
    
    // Public properties
    public $search = '';
    public $status = '';
    public $specialization = '';
    
    // Modal state
    public $showAddModal = false;
    public $showEditModal = false;
    public $editingDoctorId = null;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $phone = '';
    public $gender = 'Male';
    public $doctor_specialization = '';
    public $consultation_fee = '';
    public $location = '';
    public $experience = '';
    public $languages = '';
    public $is_available = true;
    public $mdcn_license_current = false;
    
    // Reset pagination when filters change
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatus()
    {
        $this->resetPage();
    }
    
    public function updatingSpecialization()
    {
        $this->resetPage();
    }
    
    /**
     * Open add doctor modal
     */
    public function openAddModal()
    {
        $this->reset(['name', 'email', 'phone', 'gender', 'doctor_specialization', 
                     'consultation_fee', 'location', 'experience', 'languages']);
        $this->is_available = true;
        $this->mdcn_license_current = false;
        $this->showAddModal = true;
    }
    
    /**
     * Close add modal
     */
    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetValidation();
    }
    
    /**
     * Open edit modal
     */
    public function openEditModal($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $this->editingDoctorId = $doctor->id;
        $this->name = $doctor->name;
        $this->email = $doctor->email;
        $this->phone = $doctor->phone;
        $this->gender = $doctor->gender;
        $this->doctor_specialization = $doctor->specialization ?? '';
        $this->consultation_fee = $doctor->consultation_fee ?? '';
        $this->location = $doctor->location ?? '';
        $this->experience = $doctor->experience ?? '';
        $this->languages = $doctor->languages ?? '';
        $this->is_available = $doctor->is_available ?? true;
        $this->mdcn_license_current = $doctor->mdcn_license_current ?? false;
        $this->showEditModal = true;
    }
    
    /**
     * Close edit modal
     */
    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingDoctorId = null;
        $this->resetValidation();
    }
    
    /**
     * Create new doctor
     */
    public function createDoctor()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:Male,Female',
            'doctor_specialization' => 'nullable|string|max:255',
            'consultation_fee' => 'nullable|numeric|min:0',
        ]);
        
        try {
            Doctor::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'gender' => $this->gender,
                'specialization' => $this->doctor_specialization,
                'consultation_fee' => $this->consultation_fee,
                'location' => $this->location,
                'experience' => $this->experience,
                'languages' => $this->languages,
                'is_available' => $this->is_available,
                'mdcn_license_current' => $this->mdcn_license_current,
            ]);
            
            $this->closeAddModal();
            $this->dispatch('alert', 
                message: 'Doctor added successfully!',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to add doctor: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }
    
    /**
     * Update doctor
     */
    public function updateDoctor()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email,' . $this->editingDoctorId,
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:Male,Female',
            'doctor_specialization' => 'nullable|string|max:255',
            'consultation_fee' => 'nullable|numeric|min:0',
        ]);
        
        try {
            $doctor = Doctor::findOrFail($this->editingDoctorId);
            $doctor->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'gender' => $this->gender,
                'specialization' => $this->doctor_specialization,
                'consultation_fee' => $this->consultation_fee,
                'location' => $this->location,
                'experience' => $this->experience,
                'languages' => $this->languages,
                'is_available' => $this->is_available,
                'mdcn_license_current' => $this->mdcn_license_current,
            ]);
            
            $this->closeEditModal();
            $this->dispatch('alert', 
                message: 'Doctor updated successfully!',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to update doctor: ' . $e->getMessage(),
                type: 'error'
            );
        }
    }
    
    /**
     * Toggle doctor availability
     */
    public function toggleAvailability($doctorId)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);
            $doctor->is_available = !$doctor->is_available;
            $doctor->save();
            
            $status = $doctor->is_available ? 'available' : 'unavailable';
            
            $this->dispatch('alert', 
                message: "Doctor marked as {$status}!",
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to update availability',
                type: 'error'
            );
        }
    }
    
    /**
     * Delete doctor
     */
    public function deleteDoctor($doctorId)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);
            $doctor->delete();
            
            $this->dispatch('alert', 
                message: 'Doctor deleted successfully!',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to delete doctor',
                type: 'error'
            );
        }
    }
    
    /**
     * Send campaign notification
     */
    public function sendCampaign($doctorId)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);
            Mail::to($doctor->email)->send(new CampaignNotification($doctor));
            
            $this->dispatch('alert', 
                message: 'Campaign notification sent!',
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to send notification',
                type: 'error'
            );
        }
    }
    
    /**
     * Render component
     */
    public function render()
    {
        $doctors = Doctor::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status === 'available', fn($q) => $q->where('is_available', true))
            ->when($this->status === 'unavailable', fn($q) => $q->where('is_available', false))
            ->when($this->specialization, fn($q) => $q->where('specialization', $this->specialization))
            ->latest()
            ->paginate(15);
            
        $specializations = Doctor::whereNotNull('specialization')
            ->distinct()
            ->pluck('specialization')
            ->filter();
        
        return view('livewire.admin.doctors-table', [
            'doctors' => $doctors,
            'specializations' => $specializations,
        ]);
    }
}
