<?php

namespace App\Livewire\CareGiver;

use App\Models\InboundMessage;
use App\Models\Patient;
use App\Services\VonageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CommunicationCenter extends Component
{
    // ── Patient list / selection ──
    public $patients = [];
    public $selectedPatientId = null;
    public $selectedPatient = null;

    // ── Messaging ──
    public $messages = [];
    public $newMessage = '';
    public $channel = 'sms'; // sms | whatsapp

    // ── Search ──
    public $patientSearch = '';

    // ── Quick templates ──
    public $quickTemplates = [
        'Hello, this is your caregiver checking in. How are you feeling today?',
        'Reminder: Please take your medication as prescribed.',
        'Your next vital signs check is due. I will be visiting shortly.',
        'Please let me know if you need any assistance.',
        'Your weekly health report has been sent to your family.',
    ];

    public function mount()
    {
        $this->loadPatients();

        if ($this->patients->isNotEmpty()) {
            $this->selectPatient($this->patients->first()->id);
        }
    }

    // ── Load assigned patients ──
    public function loadPatients()
    {
        $careGiver = Auth::guard('care_giver')->user();

        if (!$careGiver) {
            $this->patients = collect();
            return;
        }

        $query = $careGiver->assignedPatients()
            ->whereHas('carePlans', function ($q) {
                $q->where('status', 'active');
            });

        if ($this->patientSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->patientSearch}%")
                  ->orWhere('phone', 'like', "%{$this->patientSearch}%");
            });
        }

        $this->patients = $query->orderBy('name')->get();
    }

    // ── Select a patient thread ──
    public function selectPatient(int $id)
    {
        $this->selectedPatientId = $id;
        $this->selectedPatient = Patient::with('activeCarePlan')->find($id);
        $this->loadMessages();
    }

    // ── Load message history ──
    public function loadMessages()
    {
        if (!$this->selectedPatient) {
            $this->messages = collect();
            return;
        }

        $phone = $this->selectedPatient->phone ?? $this->selectedPatient->emergency_contact_phone;

        if (!$phone) {
            $this->messages = collect();
            return;
        }

        // Gather inbound messages from this patient + outbound tracked via audit_logs
        $this->messages = InboundMessage::where('patient_id', $this->selectedPatientId)
            ->orWhere('from_number', 'like', "%{$phone}%")
            ->orderBy('received_at', 'asc')
            ->limit(50)
            ->get();
    }

    // ── Send message ──
    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:1600',
        ]);

        if (!$this->selectedPatient) {
            $this->addError('send', 'Please select a patient first.');
            return;
        }

        $phone = $this->selectedPatient->phone
            ?? $this->selectedPatient->emergency_contact_phone;

        if (!$phone) {
            $this->addError('send', 'Patient has no phone number on file.');
            return;
        }

        try {
            $vonage = app(VonageService::class);

            $result = $this->channel === 'whatsapp'
                ? $vonage->sendWhatsAppMessage($phone, $this->newMessage)
                : $vonage->sendSMS($phone, $this->newMessage);

            if ($result['success'] ?? false) {
                // Record outbound in inbound_messages as "sent"
                InboundMessage::create([
                    'channel'      => $this->channel,
                    'message_type' => 'text',
                    'from_number'  => config('vonage.from_number', 'system'),
                    'to_number'    => $phone,
                    'message_text' => $this->newMessage,
                    'status'       => 'sent',
                    'received_at'  => now(),
                    'patient_id'   => $this->selectedPatientId,
                ]);

                $this->newMessage = '';
                $this->loadMessages();

                session()->flash('message-sent', 'Message sent successfully.');
            } else {
                $this->addError('send', $result['message'] ?? 'Failed to send message.');
            }
        } catch (\Exception $e) {
            Log::error('CommunicationCenter send failed', [
                'error' => $e->getMessage(),
                'patient_id' => $this->selectedPatientId,
            ]);
            $this->addError('send', 'An error occurred while sending.');
        }
    }

    // ── Apply quick template ──
    public function useTemplate(int $index)
    {
        if (isset($this->quickTemplates[$index])) {
            $this->newMessage = $this->quickTemplates[$index];
        }
    }

    // ── Search updated ──
    public function updatedPatientSearch()
    {
        $this->loadPatients();
    }

    public function render()
    {
        return view('livewire.care-giver.communication-center');
    }
}
