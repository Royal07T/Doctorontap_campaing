# 🎓 Livewire Implementation Guide - Complete Examples

This guide shows you exactly how to convert your existing views to Livewire + Alpine.js with real, working examples from your app.

---

## 📁 Files Created

### Layouts
- ✅ `resources/views/layouts/app-livewire.blade.php` - Master layout with Livewire

### Admin Components
- ✅ `app/Livewire/Admin/ConsultationTable.php` - Complete example
- 🔄 `app/Livewire/Admin/DoctorsTable.php` - Ready for implementation
- 🔄 `app/Livewire/Admin/NursesTable.php` - Ready for implementation
- 🔄 `app/Livewire/Admin/CanvassersTable.php` - Ready for implementation
- 🔄 `app/Livewire/Admin/PatientsTable.php` - Ready for implementation

### Doctor Components
- 🔄 `app/Livewire/Doctor/ConsultationsList.php` - Ready for implementation

### Nurse Components
- 🔄 `app/Livewire/Nurse/PatientSearch.php` - Ready for implementation

### Canvasser Components
- 🔄 `app/Livewire/Canvasser/PatientsTable.php` - Ready for implementation

---

## 🎯 Pattern 1: Data Table with Search/Filter

### Complete Example: Admin Doctors Management

#### Step 1: Livewire Component Class

```php
<?php
// File: app/Livewire/Admin/DoctorsTable.php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Doctor;
use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignNotification;

class DoctorsTable extends Component
{
    use WithPagination;
    
    // Public properties - automatically synced with frontend
    public $search = '';
    public $status = '';  // all, active, inactive
    public $specialization = '';
    
    // Modal state
    public $showAddModal = false;
    public $showEditModal = false;
    public $editingDoctorId = null;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $phone = '';
    public $doctor_specialization = '';
    
    // Reset pagination when search changes
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatus()
    {
        $this->resetPage();
    }
    
    /**
     * Open add doctor modal
     */
    public function openAddModal()
    {
        $this->reset(['name', 'email', 'phone', 'doctor_specialization']);
        $this->showAddModal = true;
    }
    
    /**
     * Close add modal
     */
    public function closeAddModal()
    {
        $this->showAddModal = false;
    }
    
    /**
     * Create new doctor
     */
    public function createDoctor()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email',
            'phone' => 'required|string',
            'doctor_specialization' => 'nullable|string',
        ]);
        
        try {
            Doctor::create([
                'full_name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'specialization' => $this->doctor_specialization,
                'is_active' => true,
            ]);
            
            $this->showAddModal = false;
            $this->reset(['name', 'email', 'phone', 'doctor_specialization']);
            
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
     * Toggle doctor status
     */
    public function toggleStatus($doctorId)
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);
            $doctor->is_active = !$doctor->is_active;
            $doctor->save();
            
            $status = $doctor->is_active ? 'activated' : 'deactivated';
            
            $this->dispatch('alert', 
                message: "Doctor {$status} successfully!",
                type: 'success'
            );
        } catch (\Exception $e) {
            $this->dispatch('alert', 
                message: 'Failed to update status',
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
            
            // Send email
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
                    $q->where('full_name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->specialization, fn($q) => $q->where('specialization', $this->specialization))
            ->latest()
            ->paginate(15);
            
        $specializations = Doctor::distinct()->pluck('specialization')->filter();
        
        return view('livewire.admin.doctors-table', [
            'doctors' => $doctors,
            'specializations' => $specializations,
        ]);
    }
}
```

#### Step 2: Livewire View

```blade
{{-- File: resources/views/livewire/admin/doctors-table.blade.php --}}
<div>
    <!-- Filters & Add Button -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="flex flex-col md:flex-row md:items-end gap-3">
            <!-- Search -->
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Search</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Name, email, phone..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
            </div>

            <!-- Status Filter -->
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Status</label>
                <select wire:model.live="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- Specialization Filter -->
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Specialization</label>
                <select wire:model.live="specialization" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white">
                    <option value="">All Specializations</option>
                    @foreach($specializations as $spec)
                        <option value="{{ $spec }}">{{ $spec }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Add Button -->
            <div>
                <button 
                    wire:click="openAddModal"
                    class="px-4 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700">
                    + Add Doctor
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed top-4 right-4 bg-purple-600 text-white px-4 py-2 rounded-lg shadow-lg z-50">
        Loading...
    </div>

    <!-- Doctors Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Specialization</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($doctors as $doctor)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">{{ $doctor->full_name }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $doctor->email }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $doctor->phone }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $doctor->specialization ?? 'General' }}</td>
                    <td class="px-4 py-3">
                        <button 
                            wire:click="toggleStatus({{ $doctor->id }})"
                            class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $doctor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 flex gap-2">
                        <button 
                            wire:click="sendCampaign({{ $doctor->id }})"
                            class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                            Send Campaign
                        </button>
                        <button 
                            wire:click="deleteDoctor({{ $doctor->id }})"
                            wire:confirm="Are you sure you want to delete this doctor?"
                            class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                            Delete
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <p class="text-lg font-semibold">No doctors found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($doctors->hasPages())
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $doctors->links() }}
        </div>
        @endif
    </div>

    <!-- Add Doctor Modal (Alpine.js) -->
    <div 
        x-data="{ show: @entangle('showAddModal') }"
        x-show="show"
        x-transition
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div class="bg-white rounded-lg max-w-md w-full p-6" @click.away="$wire.closeAddModal()">
            <h3 class="text-lg font-bold mb-4">Add New Doctor</h3>
            
            <form wire:submit.prevent="createDoctor" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Full Name *</label>
                    <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-lg">
                    @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Email *</label>
                    <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded-lg">
                    @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Phone *</label>
                    <input type="text" wire:model="phone" class="w-full px-3 py-2 border rounded-lg">
                    @error('phone') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Specialization</label>
                    <input type="text" wire:model="doctor_specialization" class="w-full px-3 py-2 border rounded-lg">
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button 
                        type="button"
                        wire:click="closeAddModal"
                        class="flex-1 px-4 py-2 bg-gray-200 rounded-lg">
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg">
                        Add Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

#### Step 3: Page View Using Component

```blade
{{-- File: resources/views/admin/doctors-livewire.blade.php --}}
<x-layouts.app-livewire>
    <x-slot name="header">Manage Doctors</x-slot>
    
    <x-slot name="sidebar">
        @include('admin.shared.sidebar', ['active' => 'doctors'])
    </x-slot>
    
    @livewire('admin.doctors-table')
</x-layouts.app-livewire>
```

#### Step 4: Add Route

```php
// File: routes/web.php
Route::get('/admin/doctors-livewire', function() {
    return view('admin.doctors-livewire');
})->name('admin.doctors.livewire')->middleware(['admin.auth', 'session.management']);
```

---

## 🎯 Pattern 2: Dashboard with Real-time Stats

### Example: Doctor Dashboard

```php
<?php
// File: app/Livewire/Doctor/Dashboard.php

namespace App\Livewire\Doctor;

use Livewire\Component;
use App\Models\Consultation;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    // Auto-refresh every 30 seconds
    protected $refresh = 30000;
    
    public function render()
    {
        $doctor = Auth::guard('doctor')->user();
        
        $stats = [
            'total_consultations' => Consultation::where('doctor_id', $doctor->id)->count(),
            'pending' => Consultation::where('doctor_id', $doctor->id)->where('status', 'pending')->count(),
            'completed_today' => Consultation::where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->whereDate('updated_at', today())
                ->count(),
            'revenue_month' => Consultation::where('doctor_id', $doctor->id)
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('consultation_fee'),
        ];
        
        $recentConsultations = Consultation::where('doctor_id', $doctor->id)
            ->latest()
            ->limit(5)
            ->get();
        
        return view('livewire.doctor.dashboard', [
            'stats' => $stats,
            'recentConsultations' => $recentConsultations,
        ]);
    }
}
```

```blade
{{-- File: resources/views/livewire/doctor/dashboard.blade.php --}}
<div wire:poll.30s>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg p-6 shadow">
            <div class="text-sm text-gray-600">Total Consultations</div>
            <div class="text-3xl font-bold text-purple-600">{{ $stats['total_consultations'] }}</div>
        </div>
        
        <div class="bg-white rounded-lg p-6 shadow">
            <div class="text-sm text-gray-600">Pending</div>
            <div class="text-3xl font-bold text-amber-600">{{ $stats['pending'] }}</div>
        </div>
        
        <div class="bg-white rounded-lg p-6 shadow">
            <div class="text-sm text-gray-600">Completed Today</div>
            <div class="text-3xl font-bold text-green-600">{{ $stats['completed_today'] }}</div>
        </div>
        
        <div class="bg-white rounded-lg p-6 shadow">
            <div class="text-sm text-gray-600">Revenue (Month)</div>
            <div class="text-3xl font-bold text-blue-600">₦{{ number_format($stats['revenue_month']) }}</div>
        </div>
    </div>

    <!-- Recent Consultations -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4">Recent Consultations</h2>
        <div class="space-y-3">
            @foreach($recentConsultations as $consultation)
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <div>
                    <div class="font-semibold">{{ $consultation->full_name }}</div>
                    <div class="text-sm text-gray-600">{{ $consultation->problem }}</div>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full
                    {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ ucfirst($consultation->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>
```

---

## 🎯 Pattern 3: Search with Instant Results

### Example: Nurse Patient Search

```php
<?php
// File: app/Livewire/Nurse/PatientSearch.php

namespace App\Livewire\Nurse;

use Livewire\Component;
use App\Models\Patient;

class PatientSearch extends Component
{
    public $search = '';
    public $results = [];
    public $selectedPatient = null;
    
    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->results = Patient::where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
                ->limit(10)
                ->get();
        } else {
            $this->results = [];
        }
    }
    
    public function selectPatient($patientId)
    {
        $this->selectedPatient = Patient::with('vitalSigns')->findOrFail($patientId);
        $this->search = $this->selectedPatient->full_name;
        $this->results = [];
    }
    
    public function render()
    {
        return view('livewire.nurse.patient-search');
    }
}
```

```blade
{{-- File: resources/views/livewire/nurse/patient-search.blade.php --}}
<div>
    <!-- Search Box -->
    <div class="relative">
        <input 
            type="text" 
            wire:model.live="search"
            placeholder="Search patients..."
            class="w-full px-4 py-3 border rounded-lg">
        
        <!-- Search Results Dropdown -->
        @if(count($results) > 0)
        <div class="absolute w-full bg-white border rounded-lg shadow-lg mt-1 z-50">
            @foreach($results as $patient)
            <button 
                wire:click="selectPatient({{ $patient->id }})"
                class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b last:border-b-0">
                <div class="font-semibold">{{ $patient->full_name }}</div>
                <div class="text-sm text-gray-600">{{ $patient->email }} • {{ $patient->phone }}</div>
            </button>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Selected Patient Details -->
    @if($selectedPatient)
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-lg mb-4">Patient Details</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-600">Name</div>
                <div class="font-semibold">{{ $selectedPatient->full_name }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600">Email</div>
                <div class="font-semibold">{{ $selectedPatient->email }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600">Phone</div>
                <div class="font-semibold">{{ $selectedPatient->phone }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600">Age</div>
                <div class="font-semibold">{{ $selectedPatient->age }}</div>
            </div>
        </div>
        
        <!-- Add Vital Signs Button -->
        <button class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg">
            Add Vital Signs
        </button>
    </div>
    @endif
</div>
```

---

## 📝 Quick Reference

### Common Livewire Directives

```blade
<!-- Two-way binding -->
<input wire:model="search" />

<!-- Live updates with debounce -->
<input wire:model.live.debounce.300ms="search" />

<!-- Click events -->
<button wire:click="save">Save</button>

<!-- Loading states -->
<div wire:loading>Loading...</div>
<button wire:loading.attr="disabled">Submit</button>

<!-- Confirmation -->
<button wire:confirm="Are you sure?">Delete</button>

<!-- Auto-refresh -->
<div wire:poll.30s>Auto updates every 30s</div>
```

### Common Alpine.js Patterns

```blade
<!-- Show/hide -->
<div x-data="{ show: false }">
    <button @click="show = !show">Toggle</button>
    <div x-show="show">Content</div>
</div>

<!-- Entangle with Livewire -->
<div x-data="{ open: @entangle('showModal') }">
    <div x-show="open">Modal content</div>
</div>

<!-- Click away -->
<div @click.away="close()">Dropdown</div>

<!-- Transitions -->
<div x-show="open" x-transition>Animated content</div>
```

---

## 🚀 Next Steps

1. **Copy the patterns above** for similar pages
2. **Test each component** individually
3. **Add real-time features** as needed
4. **Customize** for your specific needs

All your components are created and ready for implementation following these patterns!

