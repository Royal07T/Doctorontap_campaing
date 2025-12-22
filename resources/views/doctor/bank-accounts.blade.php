@extends('layouts.doctor')

@section('title', 'Bank Accounts')
@section('header-title', 'Bank Account Management')

@push('x-data-extra')
, showAddModal: false, showEditModal: false, editAccount: null
@endpush

@section('content')
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Info Alert -->
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">How Bank Verification Works</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p class="mb-2">After you add your bank account details, our admin team will verify them within 24 hours. This ensures:</p>
                                <ul class="list-disc list-inside space-y-1 ml-2">
                                    <li>Your account details are accurate</li>
                                    <li>Payments are sent to the correct account</li>
                                    <li>No typing errors in account numbers</li>
                                    <li>Your earnings are secure</li>
                                </ul>
                                <p class="mt-2 font-semibold">💡 Tip: Double-check your account number before submitting!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Bank Account Button -->
                <div class="mb-6">
                    <button @click="showAddModal = true" class="purple-gradient text-white px-6 py-3 rounded-lg hover:opacity-90 transition-all font-medium flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Bank Account</span>
                    </button>
                </div>

                <!-- Bank Accounts List -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @forelse($bankAccounts as $account)
                        <div class="bg-white rounded-lg shadow-md p-6 border-2 {{ $account->is_default ? 'border-purple-500' : 'border-gray-200' }}">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    @if($account->is_default)
                                        <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full font-semibold mb-2">DEFAULT</span>
                                    @endif
                                    @if($account->is_verified)
                                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-semibold mb-2">✓ VERIFIED</span>
                                    @else
                                        <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-semibold mb-2">PENDING VERIFICATION</span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $account->bank_name }}</h3>
                            <div class="space-y-2 text-sm text-gray-600">
                                <p><strong>Account Name:</strong> {{ $account->account_name }}</p>
                                <p><strong>Account Number:</strong> 
                                    <span x-data="{ showFull: false }">
                                        <span x-show="!showFull">{{ $account->masked_account_number }}</span>
                                        <span x-show="showFull">{{ $account->account_number }}</span>
                                        <button @click="showFull = !showFull" class="ml-2 text-purple-600 hover:text-purple-800 text-xs font-semibold">
                                            <span x-show="!showFull">Show</span>
                                            <span x-show="showFull">Hide</span>
                                        </button>
                                    </span>
                                </p>
                                @if($account->account_type)
                                    <p><strong>Account Type:</strong> {{ ucfirst($account->account_type) }}</p>
                                @endif
                            </div>

                            @if($account->verified_at)
                                <div class="mt-4 flex items-center text-xs text-green-600">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Verified on {{ $account->verified_at->format('M d, Y') }} by Admin</span>
                                </div>
                            @else
                                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded p-3">
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 text-yellow-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="text-xs text-yellow-800">
                                            <p class="font-semibold mb-1">Pending Verification</p>
                                            <p>Our admin team is verifying your account details. This usually takes less than 24 hours. You'll be able to receive payments once verified.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-6 flex space-x-2">
                                @if(!$account->is_default)
                                    <form method="POST" action="{{ route('doctor.bank-accounts.set-default', $account->id) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition-all text-sm">
                                            Set as Default
                                        </button>
                                    </form>
                                @endif
                                
                                <form method="POST" action="{{ route('doctor.bank-accounts.delete', $account->id) }}" 
                                      id="deleteForm{{ $account->id }}" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeleteBankAccount({{ $account->id }})" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-all text-sm">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white rounded-lg shadow-md p-12 text-center">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">No Bank Accounts Yet</h3>
                            <p class="text-gray-600 mb-6">Add your bank account details to receive payments for consultations.</p>
                            <button @click="showAddModal = true" class="purple-gradient text-white px-6 py-3 rounded-lg hover:opacity-90 transition-all font-medium">
                                Add Your First Bank Account
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Add Bank Account Modal -->
                <div x-show="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
                    <div @click.away="showAddModal = false" class="bg-white rounded-lg p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Add Bank Account</h2>
                            <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bank *</label>
                                    @if($banks && $banks->count() > 0)
                                        <select name="bank_id" x-model="selectedBankId" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="">Select Bank</option>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank->id }}" data-code="{{ $bank->code }}">{{ $bank->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                                            <p>No banks available. Please contact support or try refreshing the page.</p>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Number *</label>
                                    <div class="flex space-x-2">
                                        <input type="text" 
                                               name="account_number" 
                                               x-model="accountNumber"
                                               @blur="if(selectedBankId && accountNumber.length >= 10) { verifyAccount(); }"
                                               required 
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                               placeholder="Enter your account number">
                                        <button type="button" 
                                                @click="verifyAccount()" 
                                                x-show="selectedBankId && accountNumber.length >= 10"
                                                :disabled="verifying"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <span x-show="!verifying">Verify</span>
                                            <span x-show="verifying">Verifying...</span>
                                        </button>
                                    </div>
                                    <div x-show="verificationMessage" class="mt-2 text-sm" :class="verified ? 'text-green-600' : 'text-red-600'">
                                        <span x-text="verificationMessage"></span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Name *</label>
                                    <input type="text" 
                                           name="account_name" 
                                           :value="verifiedAccountName || ''"
                                           required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           :class="verified ? 'bg-green-50 border-green-300' : ''"
                                           placeholder="Will be auto-filled after verification">
                                    <p class="text-xs text-gray-500 mt-1" x-show="!verified">Account name will be auto-filled after verification</p>
                                    <p class="text-xs text-green-600 mt-1" x-show="verified">✓ Account verified! Name matches bank records.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                                    <select name="account_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">Select Type</option>
                                        <option value="savings">Savings</option>
                                        <option value="current">Current</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SWIFT Code (Optional)</label>
                                    <input type="text" name="swift_code" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                </div>
                            </div>

                            <div class="mt-6 flex space-x-4">
                                <button type="button" @click="showAddModal = false" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-medium">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        :disabled="!verified"
                                        class="flex-1 purple-gradient text-white px-6 py-3 rounded-lg hover:opacity-90 transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed">
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
                                        alpineData.verificationMessage = '✓ Account verified successfully!';
                                        alpineData.verifiedAccountName = data.data?.account_name || '';
                                        // Auto-fill account name
                                        const accountNameInput = form.querySelector('input[name="account_name"]');
                                        if (accountNameInput) {
                                            accountNameInput.value = data.data?.account_name || '';
                                        }
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

