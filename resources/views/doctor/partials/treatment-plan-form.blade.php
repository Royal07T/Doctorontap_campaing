{{-- Treatment Plan Form --}}
@props(['consultation'])

@php
    $isLocked = $consultation->treatment_plan_created;
@endphp

@if($isLocked)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
        <p class="text-sm text-yellow-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <strong>Treatment Plan Locked:</strong> This treatment plan has already been saved and cannot be edited. Once saved, it becomes a permanent medical record.
        </p>
    </div>
@endif

<form id="treatmentPlanForm" method="POST" action="{{ route('doctor.consultations.treatment-plan', $consultation->id) }}" class="space-y-6" enctype="multipart/form-data" onsubmit="return false;">
    @csrf
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Presenting Complaint -->
        <div class="md:col-span-2">
            <label for="presenting_complaint" class="block text-sm font-semibold text-gray-700 mb-2">
                Presenting Complaint <span class="text-red-500">*</span>
            </label>
            <textarea id="presenting_complaint" name="presenting_complaint" rows="3" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @if($isLocked) bg-gray-100 cursor-not-allowed @endif"
                      placeholder="Describe the main complaint or reason for consultation" @if($isLocked) disabled @endif>{{ old('presenting_complaint', $consultation->presenting_complaint ?? '') }}</textarea>
            @error('presenting_complaint')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- History of Complaint -->
        <div class="md:col-span-2">
            <label for="history_of_complaint" class="block text-sm font-semibold text-gray-700 mb-2">
                History of Complaint <span class="text-red-500">*</span>
            </label>
            <textarea id="history_of_complaint" name="history_of_complaint" rows="4" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Detailed history of the presenting complaint">{{ old('history_of_complaint', $consultation->history_of_complaint ?? '') }}</textarea>
            @error('history_of_complaint')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Past Medical History -->
        <div>
            <label for="past_medical_history" class="block text-sm font-semibold text-gray-700 mb-2">
                Past Medical History
            </label>
            <textarea id="past_medical_history" name="past_medical_history" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Previous medical conditions, surgeries, etc.">{{ old('past_medical_history', $consultation->past_medical_history ?? '') }}</textarea>
        </div>

        <!-- Family History -->
        <div>
            <label for="family_history" class="block text-sm font-semibold text-gray-700 mb-2">
                Family History
            </label>
            <textarea id="family_history" name="family_history" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Family medical history">{{ old('family_history', $consultation->family_history ?? '') }}</textarea>
        </div>

        <!-- Drug History -->
        <div>
            <label for="drug_history" class="block text-sm font-semibold text-gray-700 mb-2">
                Drug History
            </label>
            <textarea id="drug_history" name="drug_history" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Current medications, allergies, etc.">{{ old('drug_history', $consultation->drug_history ?? '') }}</textarea>
        </div>

        <!-- Social History -->
        <div>
            <label for="social_history" class="block text-sm font-semibold text-gray-700 mb-2">
                Social History
            </label>
            <textarea id="social_history" name="social_history" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Lifestyle, occupation, habits, etc.">{{ old('social_history', $consultation->social_history ?? '') }}</textarea>
        </div>

        <!-- Diagnosis -->
        <div class="md:col-span-2">
            <label for="diagnosis" class="block text-sm font-semibold text-gray-700 mb-2">
                Diagnosis <span class="text-red-500">*</span>
            </label>
            <textarea id="diagnosis" name="diagnosis" rows="3" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Medical diagnosis">{{ old('diagnosis', $consultation->diagnosis ?? '') }}</textarea>
            @error('diagnosis')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Investigation -->
        <div class="md:col-span-2">
            <label for="investigation" class="block text-sm font-semibold text-gray-700 mb-2">
                Investigation
            </label>
            <textarea id="investigation" name="investigation" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Tests, investigations, or procedures recommended">{{ old('investigation', $consultation->investigation ?? '') }}</textarea>
        </div>

        <!-- Treatment Plan -->
        <div class="md:col-span-2">
            <label for="treatment_plan" class="block text-sm font-semibold text-gray-700 mb-2">
                Treatment Plan <span class="text-red-500">*</span>
            </label>
            <textarea id="treatment_plan" name="treatment_plan" rows="5" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Detailed treatment plan and recommendations">{{ old('treatment_plan', $consultation->treatment_plan ?? '') }}</textarea>
            @error('treatment_plan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Treatment Plan Attachments -->
    <div class="border-t border-gray-200 pt-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Additional Files (Optional)</h3>
                <p class="text-sm text-gray-600 mt-1">Attach files that will be sent to the patient with the treatment plan email</p>
            </div>
        </div>
        
        <!-- File Upload -->
        <div class="mb-4">
            <label for="treatment_plan_attachments" class="block text-sm font-medium text-gray-700 mb-2">
                Upload Files
            </label>
            <input type="file" 
                   id="treatment_plan_attachments" 
                   name="treatment_plan_attachments[]" 
                   multiple
                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
            <p class="mt-1 text-xs text-gray-500">Accepted formats: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX (Max 10MB per file)</p>
        </div>

        <!-- Existing Attachments -->
        @if($consultation->treatment_plan_attachments && count($consultation->treatment_plan_attachments) > 0)
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Current Attachments:</p>
                <div class="space-y-2">
                    @foreach($consultation->treatment_plan_attachments as $attachment)
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $attachment['original_name'] ?? 'File' }}</p>
                                    <p class="text-xs text-gray-500">{{ isset($attachment['size']) ? number_format($attachment['size'] / 1024, 2) . ' KB' : '' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Prescribed Medications -->
    <div class="border-t border-gray-200 pt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Prescribed Medications</h3>
            <button type="button" id="addMedication" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm">
                + Add Medication
            </button>
        </div>
        <div id="medicationsContainer" class="space-y-4">
            @if(old('prescribed_medications') || ($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0))
                @foreach(old('prescribed_medications', $consultation->prescribed_medications ?? []) as $index => $medication)
                    <div class="medication-item bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Medication Name</label>
                                <input type="text" name="prescribed_medications[{{ $index }}][name]" value="{{ $medication['name'] ?? '' }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dosage</label>
                                <input type="text" name="prescribed_medications[{{ $index }}][dosage]" value="{{ $medication['dosage'] ?? '' }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                                <input type="text" name="prescribed_medications[{{ $index }}][frequency]" value="{{ $medication['frequency'] ?? '' }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                                <input type="text" name="prescribed_medications[{{ $index }}][duration]" value="{{ $medication['duration'] ?? '' }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>
                        <button type="button" class="removeMedication mt-2 text-sm text-red-600 hover:text-red-800">Remove</button>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Referrals Section -->
    <div class="border-t border-gray-200 pt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Referrals (Optional)</h3>
            <button type="button" id="addReferral" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm">
                + Add Referral
            </button>
        </div>
        <p class="text-sm text-gray-600 mb-4">If the patient needs to be referred to another specialist, add the referral details below.</p>
        <div id="referralsContainer" class="space-y-4">
            @if(old('referrals') || ($consultation->referrals && count($consultation->referrals) > 0))
                @foreach(old('referrals', $consultation->referrals ?? []) as $index => $referral)
                    <div class="referral-item bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Specialization <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="referrals[{{ $index }}][specialist]" 
                                       value="{{ $referral['specialist'] ?? '' }}" 
                                       required
                                       list="specializations-list-{{ $index }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                       placeholder="e.g., Cardiology, Pediatrics">
                                <datalist id="specializations-list-{{ $index }}">
                                    @php
                                        $specializations = \App\Models\Doctor::where('is_approved', true)
                                            ->where('is_available', true)
                                            ->whereNotNull('specialization')
                                            ->distinct()
                                            ->orderBy('specialization')
                                            ->pluck('specialization')
                                            ->filter();
                                    @endphp
                                    @foreach($specializations as $spec)
                                        <option value="{{ $spec }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="referrals[{{ $index }}][reason]" 
                                       value="{{ $referral['reason'] ?? '' }}" 
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                       placeholder="Reason for referral">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Urgency <span class="text-red-500">*</span></label>
                                <select name="referrals[{{ $index }}][urgency]" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option value="routine" {{ ($referral['urgency'] ?? 'routine') === 'routine' ? 'selected' : '' }}>Routine</option>
                                    <option value="urgent" {{ ($referral['urgency'] ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="emergency" {{ ($referral['urgency'] ?? '') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" class="removeReferral mt-2 text-sm text-red-600 hover:text-red-800">Remove</button>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Additional Fields -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6">
        <div>
            <label for="follow_up_instructions" class="block text-sm font-semibold text-gray-700 mb-2">
                Follow-up Instructions
            </label>
            <textarea id="follow_up_instructions" name="follow_up_instructions" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Follow-up appointment instructions">{{ old('follow_up_instructions', $consultation->follow_up_instructions ?? '') }}</textarea>
        </div>

        <div>
            <label for="lifestyle_recommendations" class="block text-sm font-semibold text-gray-700 mb-2">
                Lifestyle Recommendations
            </label>
            <textarea id="lifestyle_recommendations" name="lifestyle_recommendations" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                      placeholder="Diet, exercise, lifestyle changes">{{ old('lifestyle_recommendations', $consultation->lifestyle_recommendations ?? '') }}</textarea>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
        <a href="{{ route('doctor.consultations') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            Cancel
        </a>
        <button type="submit" id="submitTreatmentPlan" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed" @if($isLocked) disabled @endif>
            <span id="submitText">{{ $consultation->hasTreatmentPlan() ? 'Update Treatment Plan' : 'Create Treatment Plan' }}</span>
            <span id="submitLoading" class="hidden">Saving...</span>
        </button>
    </div>
</form>

<!-- Success/Error Message -->
<div id="formMessage" class="hidden mt-4 p-4 rounded-lg"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($isLocked)
    // Disable all form fields when treatment plan is locked
    const form = document.getElementById('treatmentPlanForm');
    if (form) {
        const allInputs = form.querySelectorAll('input, textarea, select, button[type="submit"]');
        allInputs.forEach(input => {
            input.disabled = true;
            input.classList.add('bg-gray-100', 'cursor-not-allowed');
        });
    }
    @endif
    
    let medicationIndex = {{ count(old('prescribed_medications', $consultation->prescribed_medications ?? [])) }};
    let referralIndex = {{ count(old('referrals', $consultation->referrals ?? [])) }};
    
    // Get specializations for datalist
    const specializations = @json(\App\Models\Doctor::where('is_approved', true)->where('is_available', true)->whereNotNull('specialization')->distinct()->orderBy('specialization')->pluck('specialization')->filter()->values());
    
    // Add referral
    document.getElementById('addReferral')?.addEventListener('click', function() {
        const container = document.getElementById('referralsContainer');
        const datalistId = `specializations-list-${referralIndex}`;
        const referralHtml = `
            <div class="referral-item bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Specialization <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="referrals[${referralIndex}][specialist]" 
                               required
                               list="${datalistId}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                               placeholder="e.g., Cardiology, Pediatrics">
                        <datalist id="${datalistId}">
                            ${specializations.map(spec => `<option value="${spec}">`).join('')}
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="referrals[${referralIndex}][reason]" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                               placeholder="Reason for referral">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urgency <span class="text-red-500">*</span></label>
                        <select name="referrals[${referralIndex}][urgency]" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="routine">Routine</option>
                            <option value="urgent">Urgent</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="removeReferral mt-2 text-sm text-red-600 hover:text-red-800">Remove</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', referralHtml);
        referralIndex++;
    });
    
    // Remove referral
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeReferral')) {
            e.target.closest('.referral-item').remove();
        }
    });
    
    // Add medication
    document.getElementById('addMedication')?.addEventListener('click', function() {
        const container = document.getElementById('medicationsContainer');
        const medicationHtml = `
            <div class="medication-item bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medication Name</label>
                        <input type="text" name="prescribed_medications[${medicationIndex}][name]" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosage</label>
                        <input type="text" name="prescribed_medications[${medicationIndex}][dosage]" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                        <input type="text" name="prescribed_medications[${medicationIndex}][frequency]" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                        <input type="text" name="prescribed_medications[${medicationIndex}][duration]" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <button type="button" class="removeMedication mt-2 text-sm text-red-600 hover:text-red-800">Remove</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', medicationHtml);
        medicationIndex++;
    });
    
    // Remove medication
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeMedication')) {
            e.target.closest('.medication-item').remove();
        }
    });
    
    // Handle form submission via AJAX
    const form = document.getElementById('treatmentPlanForm');
    
    if (!form) {
        console.error('Treatment plan form not found');
        return;
    }
    
    // Remove onsubmit attribute if it exists and prevent default
    form.removeAttribute('onsubmit');
    form.onsubmit = function(e) {
        e.preventDefault();
        return false;
    };
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('Form submission intercepted via AJAX');
        
        const submitBtn = document.getElementById('submitTreatmentPlan');
        const submitText = document.getElementById('submitText');
        const submitLoading = document.getElementById('submitLoading');
        const formMessage = document.getElementById('formMessage');
        
        // Disable submit button
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (submitText) submitText.classList.add('hidden');
        if (submitLoading) submitLoading.classList.remove('hidden');
        if (formMessage) formMessage.classList.add('hidden');
        
        // Collect form data
        const formData = new FormData(form);
        
        // Convert medications array properly
        const medications = [];
        const medicationItems = form.querySelectorAll('.medication-item');
        medicationItems.forEach((item, index) => {
            const name = item.querySelector('input[name*="[name]"]')?.value;
            const dosage = item.querySelector('input[name*="[dosage]"]')?.value;
            const frequency = item.querySelector('input[name*="[frequency]"]')?.value;
            const duration = item.querySelector('input[name*="[duration]"]')?.value;
            
            if (name && dosage && frequency && duration) {
                medications.push({ name, dosage, frequency, duration });
            }
        });
        
        // Convert referrals array properly
        const referrals = [];
        const referralItems = form.querySelectorAll('.referral-item');
        referralItems.forEach((item, index) => {
            const specialist = item.querySelector('input[name*="[specialist]"]')?.value;
            const reason = item.querySelector('input[name*="[reason]"]')?.value;
            const urgency = item.querySelector('select[name*="[urgency]"]')?.value;
            
            if (specialist && reason && urgency) {
                referrals.push({ specialist, reason, urgency });
            }
        });
        
        // Add medications and referrals as JSON strings to FormData
        if (medications.length > 0) {
            formData.set('prescribed_medications', JSON.stringify(medications));
        }
        if (referrals.length > 0) {
            formData.set('referrals', JSON.stringify(referrals));
        }
        
        console.log('Submitting treatment plan form', {
            url: form.action,
            consultation_id: {{ $consultation->id }},
            has_files: form.querySelector('#treatment_plan_attachments')?.files.length > 0
        });
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                    // Don't set Content-Type - let browser set it with boundary for multipart/form-data
                },
                body: formData
            });
            
            console.log('Response received', {
                status: response.status,
                statusText: response.statusText
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Try to use Alpine.js modal first (consultation details page)
                const alpineElement = document.querySelector('[x-data*="consultationPage"]');
                if (alpineElement && window.Alpine) {
                    const alpineData = window.Alpine.$data(alpineElement);
                    if (alpineData && typeof alpineData.showMessage === 'function') {
                        alpineData.showMessage('success', 'Success!', data.message || 'Treatment plan saved successfully!');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                        return false;
                    }
                }
                
                // Try global showAlertModal function (consultations list page)
                if (typeof showAlertModal === 'function') {
                    showAlertModal(data.message || 'Treatment plan saved successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    return false;
                }
                
                // Fallback to inline message
                if (formMessage) {
                    formMessage.className = 'mt-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800';
                    formMessage.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <strong>Success!</strong> ${data.message || 'Treatment plan saved successfully!'}
                        </div>
                    `;
                    formMessage.classList.remove('hidden');
                    formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                
                // Reload page after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                // Try to use Alpine.js modal first
                const alpineElement = document.querySelector('[x-data*="consultationPage"]');
                if (alpineElement && window.Alpine) {
                    const alpineData = window.Alpine.$data(alpineElement);
                    if (alpineData && typeof alpineData.showMessage === 'function') {
                        alpineData.showMessage('error', 'Error', data.message || 'Failed to save treatment plan. Please try again.');
                        if (submitBtn) submitBtn.disabled = false;
                        if (submitText) submitText.classList.remove('hidden');
                        if (submitLoading) submitLoading.classList.add('hidden');
                        return false;
                    }
                }
                
                // Try global showAlertModal function
                if (typeof showAlertModal === 'function') {
                    showAlertModal(data.message || 'Failed to save treatment plan. Please try again.', 'error');
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitText) submitText.classList.remove('hidden');
                    if (submitLoading) submitLoading.classList.add('hidden');
                    return false;
                }
                
                // Fallback to inline message
                if (formMessage) {
                    formMessage.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800';
                    formMessage.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <strong>Error!</strong> ${data.message || 'Failed to save treatment plan. Please try again.'}
                        </div>
                    `;
                    formMessage.classList.remove('hidden');
                }
                
                // Re-enable submit button
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.classList.remove('hidden');
                if (submitLoading) submitLoading.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error:', error);
            
            // Try to use Alpine.js modal first
            const alpineElement = document.querySelector('[x-data*="consultationPage"]');
            if (alpineElement && window.Alpine) {
                const alpineData = window.Alpine.$data(alpineElement);
                if (alpineData && typeof alpineData.showMessage === 'function') {
                    alpineData.showMessage('error', 'Error', 'An error occurred while saving. Please try again.');
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitText) submitText.classList.remove('hidden');
                    if (submitLoading) submitLoading.classList.add('hidden');
                    return false;
                }
            }
            
            // Try global showAlertModal function
            if (typeof showAlertModal === 'function') {
                showAlertModal('An error occurred while saving. Please try again.', 'error');
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.classList.remove('hidden');
                if (submitLoading) submitLoading.classList.add('hidden');
                return false;
            }
            
            // Fallback to inline message
            if (formMessage) {
                formMessage.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800';
                formMessage.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <strong>Error!</strong> An error occurred while saving. Please try again.
                    </div>
                `;
                formMessage.classList.remove('hidden');
            }
            
            // Re-enable submit button
            if (submitBtn) submitBtn.disabled = false;
            if (submitText) submitText.classList.remove('hidden');
            if (submitLoading) submitLoading.classList.add('hidden');
        }
        
        return false;
    }, true); // Use capture phase to ensure it runs first
    
    // Also prevent default on button click
    const submitButton = document.getElementById('submitTreatmentPlan');
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            // Let the form submit handler take care of it
        });
    }
});
</script>

