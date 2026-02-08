<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Email Template - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        .note-editor.note-frame {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
        }
        .note-editable {
            min-height: 400px;
        }
        .template-preview {
            border: 2px dashed #d1d5db;
            background: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .template-preview:hover {
            border-color: #9333EA;
            background: #f3e8ff;
        }
        .template-preview.selected {
            border-color: #9333EA;
            background: #f3e8ff;
            border-style: solid;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'email-templates'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('admin.shared.header', ['title' => 'Create Email Template'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                
                <div class="max-w-6xl mx-auto">
                    <div class="mb-4">
                        <a href="{{ route('admin.email-templates.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Templates
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Create New Email Template</h2>
                        <p class="text-gray-600 mb-6">Use the visual editor below - no HTML knowledge required!</p>

                        @if($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.email-templates.store') }}" method="POST" x-data="templateForm()" @submit="prepareSubmit">
                            @csrf

                            <!-- Step 1: Choose Template Style -->
                            <div class="mb-8 p-6 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border border-purple-200">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">📧 Step 1: Choose a Starting Template (Optional)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="template-preview" @click="loadTemplate('welcome')" :class="selectedTemplate === 'welcome' ? 'selected' : ''">
                                        <div class="text-center">
                                            <div class="text-3xl mb-2">👋</div>
                                            <h4 class="font-bold text-gray-900">Welcome Email</h4>
                                            <p class="text-xs text-gray-600 mt-1">Greet new patients warmly</p>
                                        </div>
                                    </div>

                                    <div class="template-preview" @click="loadTemplate('appointment')" :class="selectedTemplate === 'appointment' ? 'selected' : ''">
                                        <div class="text-center">
                                            <div class="text-3xl mb-2">📅</div>
                                            <h4 class="font-bold text-gray-900">Appointment Reminder</h4>
                                            <p class="text-xs text-gray-600 mt-1">Remind about upcoming visits</p>
                                        </div>
                                    </div>

                                    <div class="template-preview" @click="loadTemplate('newsletter')" :class="selectedTemplate === 'newsletter' ? 'selected' : ''">
                                        <div class="text-center">
                                            <div class="text-3xl mb-2">📰</div>
                                            <h4 class="font-bold text-gray-900">Newsletter</h4>
                                            <p class="text-xs text-gray-600 mt-1">Share updates & health tips</p>
                                        </div>
                                    </div>

                                    <div class="template-preview" @click="loadTemplate('promotional')" :class="selectedTemplate === 'promotional' ? 'selected' : ''">
                                        <div class="text-center">
                                            <div class="text-3xl mb-2">🎉</div>
                                            <h4 class="font-bold text-gray-900">Promotional</h4>
                                            <p class="text-xs text-gray-600 mt-1">Special offers & campaigns</p>
                                        </div>
                                    </div>

                                    <div class="template-preview" @click="loadTemplate('followup')" :class="selectedTemplate === 'followup' ? 'selected' : ''">
                                        <div class="text-center">
                                            <div class="text-3xl mb-2">💬</div>
                                            <h4 class="font-bold text-gray-900">Follow-up</h4>
                                            <p class="text-xs text-gray-600 mt-1">Check in after consultations</p>
                                        </div>
                                    </div>

                                    <div class="template-preview" @click="loadTemplate('blank')" :class="selectedTemplate === 'blank' ? 'selected' : ''">
                                        <div class="text-center">
                                            <div class="text-3xl mb-2">📝</div>
                                            <h4 class="font-bold text-gray-900">Blank</h4>
                                            <p class="text-xs text-gray-600 mt-1">Start from scratch</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Basic Information -->
                            <div class="mb-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">📝 Step 2: Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Template Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                            Template Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                            placeholder="e.g., Welcome Email">
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                            Category <span class="text-red-500">*</span>
                                        </label>
                                        <select id="category" name="category" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="">Select a category</option>
                                            <option value="marketing">Marketing</option>
                                            <option value="transactional">Transactional</option>
                                            <option value="newsletter">Newsletter</option>
                                            <option value="reminder">Reminder</option>
                                            <option value="notification">Notification</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mt-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea id="description" name="description" rows="2"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="What is this template used for?">{{ old('description') }}</textarea>
                                </div>

                                <!-- Email Subject -->
                                <div class="mt-4">
                                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Subject <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="e.g., Welcome to {company_name}!">
                                    <p class="mt-1 text-sm text-gray-500">💡 Tip: Use {variable_name} for personalization</p>
                                </div>
                            </div>

                            <!-- Step 3: Design Your Email -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-gray-900">✨ Step 3: Design Your Email</h3>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" @click="insertVariable('{name}')" class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                                            + Insert Name
                                        </button>
                                        <button type="button" @click="insertVariable('{email}')" class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                                            + Insert Email
                                        </button>
                                        <button type="button" @click="insertVariable('{date}')" class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                                            + Insert Date
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <textarea id="summernote" name="content">{{ old('content') }}</textarea>
                                    <input type="hidden" name="content_hidden" id="content_hidden">
                                </div>

                                <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-sm text-yellow-800">
                                        <strong>💡 Pro Tips:</strong>
                                    </p>
                                    <ul class="text-xs text-yellow-700 mt-2 space-y-1 ml-4 list-disc">
                                        <li>Use the toolbar above to format text (bold, colors, links, etc.)</li>
                                        <li>Insert variables like {name}, {email}, {phone} for personalization</li>
                                        <li>Add images by clicking the picture icon</li>
                                        <li>Preview your email before saving</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Common Variables Reference -->
                            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="font-medium text-blue-900 mb-3">📌 Common Variables You Can Use:</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-blue-800">
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{name}')">{name}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{first_name}')">{first_name}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{last_name}')">{last_name}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{email}')">{email}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{phone}')">{phone}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{company_name}')">{company_name}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{date}')">{date}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{time}')">{time}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{doctor_name}')">{doctor_name}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{appointment_date}')">{appointment_date}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{link}')">{link}</code>
                                    <code class="bg-white px-3 py-2 rounded cursor-pointer hover:bg-blue-100" @click="insertVariable('{unsubscribe_link}')">{unsubscribe_link}</code>
                                </div>
                                <p class="mt-2 text-xs text-blue-700">💡 Click any variable to insert it into your email</p>
                            </div>

                            <!-- Sender Settings (Collapsed) -->
                            <div class="mb-6" x-data="{ showAdvanced: false }">
                                <button type="button" @click="showAdvanced = !showAdvanced" 
                                    class="flex items-center justify-between w-full p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100">
                                    <span class="font-medium text-gray-900">⚙️ Advanced Settings (Optional)</span>
                                    <svg class="w-5 h-5 transition-transform" :class="showAdvanced ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="showAdvanced" x-collapse class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="from_name" class="block text-sm font-medium text-gray-700 mb-2">
                                                From Name
                                            </label>
                                            <input type="text" id="from_name" name="from_name" value="{{ old('from_name') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                                placeholder="e.g., DoctorOnTap Team">
                                            <p class="mt-1 text-xs text-gray-500">Leave blank for default</p>
                                        </div>

                                        <div>
                                            <label for="from_email" class="block text-sm font-medium text-gray-700 mb-2">
                                                From Email
                                            </label>
                                            <input type="email" id="from_email" name="from_email" value="{{ old('from_email') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                                placeholder="e.g., noreply@doctorontap.com">
                                            <p class="mt-1 text-xs text-gray-500">Leave blank for default</p>
                                        </div>

                                        <div>
                                            <label for="reply_to" class="block text-sm font-medium text-gray-700 mb-2">
                                                Reply-To Email
                                            </label>
                                            <input type="email" id="reply_to" name="reply_to" value="{{ old('reply_to') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                                placeholder="e.g., support@doctorontap.com">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" checked
                                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">✅ Active (Customer Care can use this template)</span>
                                </label>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                                <a href="{{ route('admin.email-templates.index') }}" 
                                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="button" @click="previewEmail()" 
                                    class="px-6 py-2 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50">
                                    👁️ Preview
                                </button>
                                <button type="submit" 
                                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Create Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- jQuery (required for Summernote) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        function templateForm() {
            return {
                selectedTemplate: 'blank',
                
                prepareSubmit(e) {
                    // Get content from Summernote before submit
                    var content = $('#summernote').summernote('code');
                    $('#content_hidden').val(content);
                },
                
                loadTemplate(type) {
                    this.selectedTemplate = type;
                    
                    const templates = {
                        welcome: `
                            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9fafb;">
                                <div style="background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <h1 style="color: #9333EA; margin-bottom: 20px;">Welcome to DoctorOnTap! 👋</h1>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Hello <strong>{name}</strong>,</p>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">We're thrilled to have you as part of our healthcare family! At DoctorOnTap, we're committed to providing you with the best medical care possible.</p>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Here's what you can do next:</p>
                                    <ul style="font-size: 16px; line-height: 1.8; color: #374151;">
                                        <li>Book your first consultation</li>
                                        <li>Complete your medical profile</li>
                                        <li>Explore our health resources</li>
                                    </ul>
                                    <div style="margin-top: 30px; text-align: center;">
                                        <a href="{link}" style="display: inline-block; padding: 12px 30px; background-color: #9333EA; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Get Started</a>
                                    </div>
                                    <p style="font-size: 14px; color: #6b7280; margin-top: 30px;">Best regards,<br>The DoctorOnTap Team</p>
                                </div>
                                <p style="font-size: 12px; color: #9ca3af; text-align: center; margin-top: 20px;">© ${new Date().getFullYear()} DoctorOnTap. All rights reserved.</p>
                            </div>
                        `,
                        appointment: `
                            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f0f9ff;">
                                <div style="background-color: white; padding: 40px; border-radius: 10px; border-left: 5px solid #3b82f6;">
                                    <h1 style="color: #1e40af; margin-bottom: 20px;">Appointment Reminder 📅</h1>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Hi <strong>{name}</strong>,</p>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">This is a friendly reminder about your upcoming appointment:</p>
                                    <div style="background-color: #dbeafe; padding: 20px; border-radius: 8px; margin: 20px 0;">
                                        <p style="margin: 5px 0; font-size: 16px;"><strong>Date:</strong> {appointment_date}</p>
                                        <p style="margin: 5px 0; font-size: 16px;"><strong>Time:</strong> {time}</p>
                                        <p style="margin: 5px 0; font-size: 16px;"><strong>Doctor:</strong> {doctor_name}</p>
                                    </div>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Please arrive 10 minutes early. If you need to reschedule, please let us know at least 24 hours in advance.</p>
                                    <div style="margin-top: 30px; text-align: center;">
                                        <a href="{link}" style="display: inline-block; padding: 12px 30px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">View Details</a>
                                    </div>
                                </div>
                            </div>
                        `,
                        newsletter: `
                            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                                <div style="background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
                                    <h1 style="color: white; margin: 0;">Health & Wellness Newsletter 📰</h1>
                                    <p style="color: white; margin-top: 10px;">{date}</p>
                                </div>
                                <div style="background-color: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Hello <strong>{name}</strong>,</p>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Welcome to this month's health newsletter! Here's what we have for you:</p>
                                    <h2 style="color: #9333EA; margin-top: 30px;">📌 Health Tips</h2>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Add your health tips and articles here...</p>
                                    <h2 style="color: #9333EA; margin-top: 30px;">🏥 Latest Updates</h2>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Share clinic updates and news...</p>
                                    <div style="margin-top: 30px; text-align: center;">
                                        <a href="{link}" style="display: inline-block; padding: 12px 30px; background-color: #9333EA; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Read More</a>
                                    </div>
                                </div>
                                <p style="font-size: 12px; color: #9ca3af; text-align: center; margin-top: 20px;"><a href="{unsubscribe_link}" style="color: #9ca3af;">Unsubscribe</a></p>
                            </div>
                        `,
                        promotional: `
                            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #fef3c7;">
                                <div style="background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                    <div style="text-align: center; margin-bottom: 30px;">
                                        <h1 style="color: #f59e0b; margin-bottom: 10px;">🎉 Special Offer Just for You!</h1>
                                        <p style="font-size: 20px; color: #92400e; font-weight: bold;">Limited Time Only</p>
                                    </div>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Hi <strong>{name}</strong>,</p>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">We have an exclusive offer just for you!</p>
                                    <div style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); padding: 30px; border-radius: 8px; text-align: center; margin: 30px 0;">
                                        <h2 style="color: white; font-size: 28px; margin: 0;">20% OFF</h2>
                                        <p style="color: white; font-size: 18px; margin-top: 10px;">Your Next Consultation</p>
                                    </div>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Don't miss this opportunity to prioritize your health at a great value!</p>
                                    <div style="margin-top: 30px; text-align: center;">
                                        <a href="{link}" style="display: inline-block; padding: 15px 40px; background-color: #f59e0b; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 18px;">Claim Offer</a>
                                    </div>
                                    <p style="font-size: 12px; color: #6b7280; text-align: center; margin-top: 20px;">*Offer expires soon. Terms and conditions apply.</p>
                                </div>
                            </div>
                        `,
                        followup: `
                            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9fafb;">
                                <div style="background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <h1 style="color: #059669; margin-bottom: 20px;">How Are You Feeling? 💬</h1>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Hello <strong>{name}</strong>,</p>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">We hope you're doing well! We wanted to check in and see how you're feeling after your recent consultation with {doctor_name}.</p>
                                    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Your feedback helps us provide better care. Please let us know:</p>
                                    <ul style="font-size: 16px; line-height: 1.8; color: #374151;">
                                        <li>How are you feeling now?</li>
                                        <li>Are you following the treatment plan?</li>
                                        <li>Do you have any concerns or questions?</li>
                                    </ul>
                                    <div style="margin-top: 30px; text-align: center;">
                                        <a href="{link}" style="display: inline-block; padding: 12px 30px; background-color: #059669; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Provide Feedback</a>
                                    </div>
                                    <p style="font-size: 14px; color: #6b7280; margin-top: 30px;">We're here to support your health journey every step of the way.</p>
                                </div>
                            </div>
                        `,
                        blank: '<p>Start creating your email here...</p>'
                    };
                    
                    $('#summernote').summernote('code', templates[type]);
                },
                
                insertVariable(variable) {
                    $('#summernote').summernote('editor.insertText', variable);
                },
                
                previewEmail() {
                    var content = $('#summernote').summernote('code');
                    var subject = document.getElementById('subject').value;
                    
                    // Open preview in new window
                    var previewWindow = window.open('', 'Email Preview', 'width=800,height=600');
                    previewWindow.document.write(`
                        <html>
                        <head>
                            <title>Email Preview</title>
                            <style>
                                body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
                                .preview-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                                .subject-line { font-size: 18px; font-weight: bold; padding: 10px; background: #f9fafb; border-left: 4px solid #9333EA; margin-bottom: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class="preview-container">
                                <div class="subject-line">Subject: ${subject || 'No subject'}</div>
                                ${content}
                            </div>
                        </body>
                        </html>
                    `);
                }
            }
        }

        // Initialize Summernote
        $(document).ready(function() {
            $('#summernote').summernote({
                height: 400,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                placeholder: 'Start designing your email here...',
                tabsize: 2,
                callbacks: {
                    onInit: function() {
                        // Load blank template by default
                        setTimeout(function() {
                            $('#summernote').summernote('code', '<p>Start creating your email here...</p>');
                        }, 100);
                    }
                }
            });
        });
    </script>
</body>
</html>
