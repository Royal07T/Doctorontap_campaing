<!-- Custom Alert Modal -->
<div id="customAlertModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) CustomAlert.close()">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <!-- Icon -->
            <div id="alertIconContainer" class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full">
                <svg id="alertIcon" class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <!-- Default error icon -->
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            
            <!-- Message -->
            <h3 id="alertTitle" class="text-lg font-semibold text-gray-900 text-center mb-2"></h3>
            <p id="alertMessage" class="text-sm text-gray-600 text-center mb-6 whitespace-pre-line"></p>
            
            <!-- Buttons -->
            <div id="alertButtons" class="flex justify-center space-x-3">
                <!-- Buttons will be dynamically inserted here -->
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirm Modal -->
<div id="customConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) CustomAlert.closeConfirm()">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <!-- Icon -->
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-100">
                <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            
            <!-- Message -->
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Confirm Action</h3>
            <p id="confirmMessage" class="text-sm text-gray-600 text-center mb-6 whitespace-pre-line"></p>
            
            <!-- Buttons -->
            <div class="flex justify-center space-x-3">
                <button onclick="CustomAlert.closeConfirm()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition-colors">
                    Cancel
                </button>
                <button id="confirmButton" onclick="CustomAlert.executeConfirm()" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Custom Alert Utility
window.CustomAlert = {
    confirmCallback: null,
    onCloseCallback: null,
    
    show(message, type = 'info', title = null, onClose = null) {
        const modal = document.getElementById('customAlertModal');
        const iconContainer = document.getElementById('alertIconContainer');
        const icon = document.getElementById('alertIcon');
        const titleEl = document.getElementById('alertTitle');
        const messageEl = document.getElementById('alertMessage');
        const buttonsEl = document.getElementById('alertButtons');
        
        // Set message
        messageEl.textContent = message;
        
        // Set title
        if (title) {
            titleEl.textContent = title;
            titleEl.classList.remove('hidden');
        } else {
            titleEl.classList.add('hidden');
        }
        
        // Configure based on type
        let iconPath, bgColor, textColor, buttonColor, buttonText;
        
        switch(type) {
            case 'success':
                iconPath = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
                bgColor = 'bg-green-100';
                textColor = 'text-green-600';
                buttonColor = 'bg-green-600 hover:bg-green-700';
                buttonText = 'OK';
                break;
            case 'error':
                iconPath = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                bgColor = 'bg-red-100';
                textColor = 'text-red-600';
                buttonColor = 'bg-red-600 hover:bg-red-700';
                buttonText = 'OK';
                break;
            case 'warning':
                iconPath = '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>';
                bgColor = 'bg-yellow-100';
                textColor = 'text-yellow-600';
                buttonColor = 'bg-yellow-600 hover:bg-yellow-700';
                buttonText = 'OK';
                break;
            default: // info
                iconPath = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>';
                bgColor = 'bg-blue-100';
                textColor = 'text-blue-600';
                buttonColor = 'bg-blue-600 hover:bg-blue-700';
                buttonText = 'OK';
        }
        
        this.onCloseCallback = onClose;
        
        iconContainer.className = `flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full ${bgColor}`;
        icon.className = `w-8 h-8 ${textColor}`;
        icon.innerHTML = iconPath;
        
        // Set button
        buttonsEl.innerHTML = `
            <button onclick="CustomAlert.close()" class="px-6 py-2 ${buttonColor} text-white rounded-lg font-medium transition-colors">
                ${buttonText}
            </button>
        `;
        
        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    },
    
    close() {
        const modal = document.getElementById('customAlertModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        if (this.onCloseCallback) {
            this.onCloseCallback();
            this.onCloseCallback = null;
        }
    },
    
    confirm(message, onConfirm, onCancel = null) {
        const modal = document.getElementById('customConfirmModal');
        const messageEl = document.getElementById('confirmMessage');
        
        messageEl.textContent = message;
        this.confirmCallback = onConfirm;
        this.cancelCallback = onCancel;
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    },
    
    executeConfirm() {
        if (this.confirmCallback) {
            this.confirmCallback();
        }
        this.closeConfirm();
    },
    
    closeConfirm() {
        const modal = document.getElementById('customConfirmModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        this.confirmCallback = null;
        this.cancelCallback = null;
    },
    
    // Helper methods for common use cases
    success(message, title = 'Success', onClose = null) {
        this.show(message, 'success', title, onClose);
    },
    
    error(message, title = 'Error', onClose = null) {
        this.show(message, 'error', title, onClose);
    },
    
    warning(message, title = 'Warning', onClose = null) {
        this.show(message, 'warning', title, onClose);
    },
    
    info(message, title = 'Information', onClose = null) {
        this.show(message, 'info', title, onClose);
    }
};

// Override native alert and confirm (optional - for backward compatibility)
window.originalAlert = window.alert;
window.originalConfirm = window.confirm;

window.alert = function(message) {
    CustomAlert.info(message);
};

window.confirm = function(message) {
    return new Promise((resolve) => {
        CustomAlert.confirm(message, () => resolve(true), () => resolve(false));
    });
};
</script>
