@extends('layouts.doctor')

@section('title', 'Bank Accounts')
@section('header-title', 'Bank Account Management')

@push('x-data-extra')
, showAddModal: false, showEditModal: false, editAccount: null
@endpush

@section('content')
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 px-4 py-3 rounded-lg relative" role="alert">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-medium">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Info Alert -->
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-xl">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-4 w-4 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-xs font-semibold text-blue-900 uppercase tracking-wide mb-1.5">How Bank Verification Works</h3>
                            <div class="text-xs text-blue-700 leading-relaxed">
                                <p class="mb-1.5">After you add your bank account details, our admin team will verify them within 24 hours. This ensures:</p>
                                <ul class="list-disc list-inside space-y-0.5 ml-1">
                                    <li>Your account details are accurate</li>
                                    <li>Payments are sent to the correct account</li>
                                    <li>No typing errors in account numbers</li>
                                    <li>Your earnings are secure</li>
                                </ul>
                                <p class="mt-1.5 font-semibold">💡 Tip: Double-check your account number before submitting!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Bank Account Button -->
                <div class="mb-6">
                    <button @click="showAddModal = true" class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Bank Account</span>
                    </button>
                </div>

                <!-- Bank Accounts List -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @forelse($bankAccounts as $account)
                        <div class="bg-white rounded-xl shadow-sm p-5 border-2 {{ $account->is_default ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200' }} hover:shadow-lg transition-all relative">
                            <!-- Security Badge -->
                            @if($account->is_verified)
                            <div class="absolute top-4 right-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 border-2 border-emerald-300 flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            @endif
                            
                            <div class="flex justify-between items-start mb-3 pr-12">
                                <div class="flex flex-wrap gap-1.5">
                                    @if($account->is_default)
                                        <span class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-full font-bold uppercase tracking-wider border border-indigo-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                            Default
                                        </span>
                                    @endif
                                    @if($account->is_verified)
                                        <span class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-700 text-xs rounded-full font-bold uppercase tracking-wider border border-emerald-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-bold uppercase tracking-wider border border-amber-200">
                                            Pending
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $account->bank_name }}</h3>
                            <div class="space-y-3 text-sm mb-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Account Name</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $account->account_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Account Number</p>
                                    <div class="flex items-center gap-2">
                                        <span x-data="{ showFull: false }" class="flex items-center gap-2">
                                            <span x-show="!showFull" class="font-mono text-sm font-bold text-gray-900">{{ $account->masked_account_number }}</span>
                                            <span x-show="showFull" class="font-mono text-sm font-bold text-gray-900">{{ $account->account_number }}</span>
                                            <button @click="showFull = !showFull" 
                                                    class="px-2 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded transition-colors flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <span x-text="showFull ? 'Hide' : 'Show'"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                @if($account->account_type)
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Account Type</p>
                                    <p class="text-sm text-gray-900">{{ ucfirst($account->account_type) }}</p>
                                </div>
                                @endif
                            </div>

                            @if($account->verified_at)
                                <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                                    <div class="flex items-center gap-2 text-xs text-emerald-700">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-semibold">Verified on {{ $account->verified_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="mb-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="text-xs text-amber-800 leading-relaxed">
                                            <p class="font-bold mb-1">Pending Verification</p>
                                            <p>Admin team is verifying your account. Usually takes less than 24 hours.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-5 pt-4 border-t border-gray-100 flex space-x-1.5">
                                @if(!$account->is_default)
                                    <form method="POST" action="{{ route('doctor.bank-accounts.set-default', $account->id) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-0.5 px-1.5 py-1 text-[9px] font-medium text-white purple-gradient rounded hover:opacity-90 transition">
                                            <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Set Default
                                        </button>
                                    </form>
                                @endif
                                
                                <form method="POST" action="{{ route('doctor.bank-accounts.delete', $account->id) }}" 
                                      id="deleteForm{{ $account->id }}" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeleteBankAccount({{ $account->id }})" 
                                            class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-all shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Bank Accounts Yet</h3>
                            <p class="text-xs text-gray-500 mb-6">Add your bank account details to receive payments for consultations.</p>
                            <button @click="showAddModal = true" class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Your First Bank Account
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Add Bank Account Modal -->
                <div x-show="showAddModal" 
                     x-cloak
                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                     style="display: none;">
                    <div @click.away="showAddModal = false" class="bg-white rounded-xl shadow-2xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900">Add Bank Account</h2>
                            <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('doctor.bank-accounts.store') }}" id="addBankAccountForm" x-data="{ 
                            selectedBankId: '', 
                            accountNumber: '', 
                            verifying: false, 
                            verified: false, 
                            verificationMessage: '',
                            verifiedAccountName: ''
                        }">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Bank *</label>
                                    @if($banks && $banks->count() > 0)
                                        <select name="bank_id" x-model="selectedBankId" required class="w-full text-sm px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition">
                                            <option value="">Select Bank</option>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}" data-code="{{ $bank->code }}">{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                                            <p>No banks available. Please contact support or try refreshing the page.</p>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Account Number *</label>
                                    <div class="flex space-x-2">
                                        <input type="text" 
                                               name="account_number" 
                                               x-model="accountNumber"
                                               @blur="if(selectedBankId && accountNumber.length >= 10) { verifyAccount(); }"
                                               required 
                                               class="flex-1 text-sm px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition"
                                               placeholder="Enter your account number">
                                        <button type="button" 
                                                @click="verifyAccount()" 
                                                x-show="selectedBankId && accountNumber.length >= 10"
                                                :disabled="verifying"
                                                class="px-4 py-2.5 text-xs font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                            <span x-show="!verifying">Verify</span>
                                            <span x-show="verifying">Verifying...</span>
                                        </button>
                                    </div>
                                    <div x-show="verificationMessage" class="mt-1.5 text-xs font-medium" :class="verified ? 'text-emerald-600' : 'text-red-600'">
                                        <span x-text="verificationMessage"></span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Account Name *</label>
                                    <input type="text" 
                                           name="account_name" 
                                           :value="verifiedAccountName || ''"
                                           required 
                                           class="w-full text-sm px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition"
                                           :class="verified ? 'bg-emerald-50 border-emerald-300' : ''"
                                           placeholder="Will be auto-filled after verification">
                                    <p class="text-xs text-gray-500 mt-1" x-show="!verified">Account name will be auto-filled after verification</p>
                                    <p class="text-xs text-emerald-600 mt-1 font-medium" x-show="verified">✓ Account verified! Name matches bank records.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Account Type</label>
                                    <select name="account_type" class="w-full text-sm px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition">
                                        <option value="">Select Type</option>
                                        <option value="savings">Savings</option>
                                        <option value="current">Current</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">SWIFT Code (Optional)</label>
                                    <input type="text" name="swift_code" class="w-full text-sm px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Notes (Optional)</label>
                                    <textarea name="notes" rows="3" class="w-full text-sm px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition"></textarea>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-200 flex space-x-3">
                                <button type="button" @click="showAddModal = false" class="flex-1 px-5 py-2.5 text-xs font-semibold border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        :disabled="!verified"
                                        class="flex-1 px-5 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    Add Bank Account
                                </button>
                            </div>
                        </form>

                        <script>
                            // Make verifyAccount function available globally for Alpine.js
                            window.verifyAccount = function() {
                                const form = document.getElementById('addBankAccountForm');
                                if (!form) return;
                                
                                const alpineData = Alpine.$data(form);
                                const bankSelect = form.querySelector('select[name="bank_id"]');
                                const accountNumberInput = form.querySelector('input[name="account_number"]');
                                
                                if (!bankSelect || !accountNumberInput) return;
                                
                                const selectedOption = bankSelect.options[bankSelect.selectedIndex];
                                const bankCode = selectedOption.getAttribute('data-code');
                                const accountNumber = accountNumberInput.value;

                                if (!bankCode || !accountNumber || accountNumber.length < 10) {
                                    alpineData.verificationMessage = 'Please select a bank and enter a valid account number (at least 10 digits)';
                                    return;
                                }

                                alpineData.verifying = true;
                                alpineData.verificationMessage = '';
                                alpineData.verified = false;

                                fetch('{{ route("doctor.banks.verify-account") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        bank_code: bankCode,
                                        account_number: accountNumber
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    alpineData.verifying = false;
                                    if (data.success) {
                                        alpineData.verified = true;
                                        alpineData.verificationMessage = '✓ Account verified successfully via KoraPay!';
                                        alpineData.verifiedAccountName = data.data?.account_name || '';
                                        // Auto-fill account name
                                        const accountNameInput = form.querySelector('input[name="account_name"]');
                                        if (accountNameInput) {
                                            accountNameInput.value = data.data?.account_name || '';
                                        }
                                        // Show success message
                                        setTimeout(() => {
                                            const successMsg = document.createElement('div');
                                            successMsg.className = 'fixed top-4 right-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg shadow-lg z-50';
                                            successMsg.innerHTML = '<div class="flex items-center gap-2"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg><span class="font-semibold">Account verified successfully!</span></div>';
                                            document.body.appendChild(successMsg);
                                            setTimeout(() => successMsg.remove(), 3000);
                                        }, 100);
                                    } else {
                                        alpineData.verified = false;
                                        alpineData.verificationMessage = '✗ ' + data.message;
                                    }
                                })
                                .catch(error => {
                                    alpineData.verifying = false;
                                    alpineData.verified = false;
                                    alpineData.verificationMessage = '✗ Verification failed. Please try again.';
                                    console.error('Verification error:', error);
                                });
                            };
                        </script>
                    </div>
                </div>
@endsection

@push('scripts')
    @include('components.alert-modal')

    <script>
        function confirmDeleteBankAccount(accountId) {
            // Use custom confirm modal
            if (typeof showConfirmModal === 'function') {
                showConfirmModal('Are you sure you want to delete this bank account? This action cannot be undone.', () => {
                    document.getElementById('deleteForm' + accountId).submit();
                });
            }
        }
    </script>
@endpush

