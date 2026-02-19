<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Template - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        .variable-btn {
            transition: all 0.2s;
        }
        .variable-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .note-editor {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, channel: '{{ $communicationTemplate->channel }}', body: @js($communicationTemplate->body), detectedVars: [], showVariables: false }" 
      x-init="$watch('body', value => {
          const matches = value.match(/\{\{(\w+)\}\}/g) || [];
          detectedVars = matches.map(m => m.replace(/\{\{|\}\}/g, ''));
      });
      $watch('channel', value => {
          if (value === 'email') {
              setTimeout(() => {
                  if ($('#summernote').length && typeof $('#summernote').summernote === 'function') {
                      $('#summernote').summernote('code', body || '<p>Hello @{{first_name}},</p><p>Your message here...</p>');
                  }
              }, 100);
          }
      })">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'communication-templates'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('admin.shared.header', ['title' => 'Edit Communication Template'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-6">
                        <a href="{{ route('admin.communication-templates.show', $communicationTemplate) }}" 
                           class="inline-flex items-center gap-2 text-sm font-semibold text-purple-600 hover:text-purple-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Template
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Template</h2>

                        @if($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.communication-templates.update', $communicationTemplate) }}">
                            @csrf
                            @method('PUT')

                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Template Name *</label>
                                        <input type="text" name="name" value="{{ old('name', $communicationTemplate->name) }}" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        @error('name')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Channel *</label>
                                        <select name="channel" x-model="channel" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="sms" {{ $communicationTemplate->channel === 'sms' ? 'selected' : '' }}>SMS</option>
                                            <option value="email" {{ $communicationTemplate->channel === 'email' ? 'selected' : '' }}>Email</option>
                                            <option value="whatsapp" {{ $communicationTemplate->channel === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                        </select>
                                        @error('channel')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div x-show="channel === 'email'">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Subject *</label>
                                    <input type="text" name="subject" value="{{ old('subject', $communicationTemplate->subject) }}" 
                                           :required="channel === 'email'"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('subject')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-gray-700">Message Body *</label>
                                        <button type="button" @click="showVariables = !showVariables" 
                                                class="text-xs px-3 py-1 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors font-semibold">
                                            <span x-show="!showVariables">📋 Show Variables</span>
                                            <span x-show="showVariables">✕ Hide Variables</span>
                                        </button>
                                    </div>
                                    
                                    <!-- Variable Insertion Panel -->
                                    <div x-show="showVariables" class="mb-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                        <p class="text-xs font-semibold text-gray-700 mb-2">Click to insert variables:</p>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                            <button type="button" @click="insertVariable('@{{first_name}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{first_name}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{last_name}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{last_name}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{full_name}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{full_name}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{email}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{email}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{phone}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{phone}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{phone_formatted}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{phone_formatted}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{age}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{age}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{gender}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{gender}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{reference}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{reference}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{doctor_name}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{doctor_name}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{scheduled_date}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{scheduled_date}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{scheduled_time}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{scheduled_time}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{company_name}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{company_name}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{company_phone}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{company_phone}}
                                            </button>
                                            <button type="button" @click="insertVariable('@{{current_date}}')" 
                                                    class="variable-btn px-3 py-2 text-xs bg-white border border-gray-300 rounded hover:bg-purple-50 hover:border-purple-300 text-gray-700 font-medium">
                                                @{{current_date}}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Email: WYSIWYG Editor -->
                                    <div x-show="channel === 'email'">
                                        <textarea id="summernote" name="body" required>{{ old('body', $communicationTemplate->body) }}</textarea>
                                        <p class="mt-2 text-xs text-gray-500">
                                            💡 Use the visual editor above to format your email. Variables like @{{first_name}} will be automatically replaced.
                                        </p>
                                    </div>
                                    
                                    <!-- SMS/WhatsApp: Simple Textarea -->
                                    <div x-show="channel !== 'email'">
                                        <textarea name="body" x-model="body" rows="6" required
                                                  placeholder="Enter your message. Click variables above to insert them..."
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('body', $communicationTemplate->body) }}</textarea>
                                        <p class="mt-2 text-xs text-gray-500">
                                            💡 Keep SMS messages under 160 characters. WhatsApp messages can be longer.
                                        </p>
                                    </div>
                                    
                                    <!-- Detected Variables Display -->
                                    <div x-show="detectedVars.length > 0" class="mt-3 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                        <p class="text-xs font-semibold text-purple-700 mb-2">✓ Detected Variables in your template:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="var in detectedVars" :key="var">
                                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded font-mono" x-text="'{{' + var + '}}'"></span>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    @error('body')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="active" value="1" {{ old('active', $communicationTemplate->active) ? 'checked' : '' }}
                                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                        <span class="text-sm font-semibold text-gray-700">Active (available for use)</span>
                                    </label>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                                    <a href="{{ route('admin.communication-templates.show', $communicationTemplate) }}" 
                                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </a>
                                    <button type="submit" 
                                            class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                                        Update Template
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Summernote JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <script>
        // Initialize Summernote for email channel
        document.addEventListener('DOMContentLoaded', function() {
            const channelSelect = document.querySelector('[name="channel"]');
            let summernoteInitialized = false;
            const initialContent = @js(old('body', $communicationTemplate->body));
            
            function initSummernote() {
                if (document.querySelector('[name="channel"]').value === 'email' && !summernoteInitialized) {
                    $('#summernote').summernote({
                        height: 400,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                            ['fontname', ['fontname']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'Start creating your email template here...',
                        tabsize: 2,
                        callbacks: {
                            onChange: function(contents) {
                                // Content is automatically saved to textarea
                            },
                            onInit: function() {
                                // Load existing content
                                if (initialContent) {
                                    $('#summernote').summernote('code', initialContent);
                                }
                            }
                        }
                    });
                    summernoteInitialized = true;
                }
            }
            
            // Initialize on page load if email is selected
            if (channelSelect.value === 'email') {
                setTimeout(initSummernote, 100);
            }
            
            // Watch for channel changes
            channelSelect.addEventListener('change', function() {
                if (this.value === 'email' && !summernoteInitialized) {
                    setTimeout(initSummernote, 100);
                } else if (this.value !== 'email' && summernoteInitialized) {
                    // Destroy summernote if switching away from email
                    $('#summernote').summernote('destroy');
                    summernoteInitialized = false;
                }
            });
        });
        
        // Variable insertion function
        function insertVariable(variable) {
            const channel = document.querySelector('[name="channel"]').value;
            
            if (channel === 'email') {
                // Insert into Summernote
                if ($('#summernote').length && typeof $('#summernote').summernote === 'function') {
                    $('#summernote').summernote('insertText', variable);
                }
            } else {
                // Insert into textarea
                const textarea = document.querySelector('[name="body"]');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const text = textarea.value;
                const before = text.substring(0, start);
                const after = text.substring(end, text.length);
                
                textarea.value = before + variable + after;
                textarea.selectionStart = textarea.selectionEnd = start + variable.length;
                textarea.focus();
                
                // Trigger Alpine.js update
                textarea.dispatchEvent(new Event('input'));
            }
        }
        
        // Make insertVariable available globally
        window.insertVariable = insertVariable;
        
        // Update form submission to get content from Summernote
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const channel = document.querySelector('[name="channel"]').value;
                    if (channel === 'email') {
                        // Get content from Summernote
                        const content = $('#summernote').summernote('code');
                        // Update the body input
                        const bodyInput = form.querySelector('[name="body"]');
                        if (bodyInput) {
                            bodyInput.value = content;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>

