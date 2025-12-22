<!-- Alert Modal -->
<div id="alertModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" onclick="closeAlertModal()"></div>
        
        <!-- Modal Content -->
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 transform transition-all z-10">
            <!-- Icon -->
            <div id="alertIconContainer" class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
                <svg id="alertIcon" class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            <!-- Content -->
            <div class="text-center">
                <h3 id="alertTitle" class="text-xl font-bold text-gray-900 mb-2">Alert</h3>
                <p id="alertMessage" class="text-gray-600 mb-6"></p>
            </div>

            <!-- Button -->
            <button onclick="closeAlertModal()" 
                    class="w-full px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 hover:shadow-lg transition-all">
                OK
            </button>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
        
        <!-- Modal Content -->
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 transform transition-all z-10">
            <!-- Icon -->
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-100">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>

            <!-- Content -->
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Confirm Action</h3>
                <p id="confirmMessage" class="text-gray-600"></p>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-3">
                <button onclick="closeConfirmModal()" 
                        class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition-all">
                    Cancel
                </button>
                <button onclick="executeConfirmAction()" 
                        class="flex-1 px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 hover:shadow-lg transition-all">
                    Confirm
                </button>
        </div>
    </div>
</div>

<script>
    // Global alert and confirm modal functions
    let confirmCallback = null;

    function showAlertModal(message, type = 'error', title = null) {
        const modal = document.getElementById('alertModal');
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const messageEl = document.getElementById('alertMessage');
        const titleEl = document.getElementById('alertTitle');
        
        messageEl.textContent = message;
        titleEl.textContent = title || (type === 'success' ? 'Success' : type === 'info' ? 'Information' : 'Error');
        
        // Set icon and colors based on type
        if (type === 'success') {
            iconContainer.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
            icon.className = 'w-8 h-8 text-green-600';
        } else if (type === 'info') {
            iconContainer.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            icon.className = 'w-8 h-8 text-blue-600';
        } else {
            iconContainer.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
            icon.className = 'w-8 h-8 text-red-600';
        }
        
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeAlertModal() {
        const modal = document.getElementById('alertModal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function showConfirmModal(message, onConfirm) {
        const modal = document.getElementById('confirmModal');
        const messageEl = document.getElementById('confirmMessage');
        
        messageEl.textContent = message;
        confirmCallback = onConfirm;
        
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirmModal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        confirmCallback = null;
    }

    function executeConfirmAction() {
        if (confirmCallback && typeof confirmCallback === 'function') {
            confirmCallback();
        }
        closeConfirmModal();
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAlertModal();
            closeConfirmModal();
        }
    });

    // Replace browser alert and confirm with custom modals (only if functions are available)
    if (typeof window.originalAlert === 'undefined') {
        window.originalAlert = window.alert;
    }
    if (typeof window.originalConfirm === 'undefined') {
        window.originalConfirm = window.confirm;
    }
    
    // Override alert() globally
    window.alert = function(message) {
        if (typeof showAlertModal === 'function') {
            showAlertModal(message, 'info');
        } else {
            window.originalAlert(message);
        }
    };
    
    // Override confirm() globally - Note: This returns a Promise, not a boolean
    // For synchronous code that expects boolean, use showConfirmModal directly
    window.confirm = function(message) {
        if (typeof showConfirmModal === 'function') {
            return new Promise((resolve) => {
                showConfirmModal(message, () => {
                    resolve(true);
                });
                // Note: This doesn't perfectly replicate confirm() synchronous behavior
                // For code that needs synchronous boolean return, use showConfirmModal directly
            });
        } else {
            return window.originalConfirm(message);
        }
    };
</script>
