@php
    // Automatically detect the page title based on the current route
    $routeName = request()->route()->getName();
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Control Center';
    
    $titles = [
        'customer-care.dashboard' => ['title' => 'Dashboard', 'subtitle' => 'Control Center'],
        'customer-care.consultations' => ['title' => 'Consultations', 'subtitle' => 'Manage Patient Consultations'],
        'customer-care.consultations.show' => ['title' => 'Consultation Details', 'subtitle' => 'View Consultation Information'],
        'customer-care.interactions.index' => ['title' => 'Interactions', 'subtitle' => 'Customer Interactions'],
        'customer-care.interactions.create' => ['title' => 'New Interaction', 'subtitle' => 'Create Customer Interaction'],
        'customer-care.tickets.index' => ['title' => 'Support Tickets', 'subtitle' => 'Manage Support Requests'],
        'customer-care.tickets.create' => ['title' => 'Create Ticket', 'subtitle' => 'New Support Request'],
        'customer-care.tickets.show' => ['title' => 'Ticket Details', 'subtitle' => 'View Support Ticket'],
        'customer-care.escalations.index' => ['title' => 'Escalations', 'subtitle' => 'Escalated Cases'],
        'customer-care.escalations.show' => ['title' => 'Escalation Details', 'subtitle' => 'View Escalated Case'],
        'customer-care.customers.index' => ['title' => 'Patients', 'subtitle' => 'Patient Management'],
        'customer-care.customers.show' => ['title' => 'Patient Profile', 'subtitle' => 'View Patient Information'],
        'customer-care.doctors.index' => ['title' => 'Doctors', 'subtitle' => 'Doctor Directory'],
        'customer-care.doctors.show' => ['title' => 'Doctor Profile', 'subtitle' => 'View Doctor Information'],
        'customer-care.bulk-sms.index' => ['title' => 'Bulk SMS', 'subtitle' => 'SMS Marketing Campaigns'],
        'customer-care.bulk-sms.create' => ['title' => 'Send Bulk SMS', 'subtitle' => 'Create SMS Campaign'],
        'customer-care.bulk-sms.show' => ['title' => 'SMS Campaign', 'subtitle' => 'View Campaign Details'],
        'customer-care.bulk-email.index' => ['title' => 'Bulk Email', 'subtitle' => 'Email Marketing Campaigns'],
        'customer-care.bulk-email.create' => ['title' => 'Send Bulk Email', 'subtitle' => 'Create Email Campaign'],
        'customer-care.bulk-email.show' => ['title' => 'Email Campaign', 'subtitle' => 'View Campaign Details'],
    ];
    
    if (isset($titles[$routeName])) {
        $pageTitle = $titles[$routeName]['title'];
        $pageSubtitle = $titles[$routeName]['subtitle'];
    } elseif (isset($title)) {
        $pageTitle = $title;
    }
@endphp

<header class="bg-gradient-to-r from-purple-50 to-indigo-50 border-b-2 border-purple-200 z-10 sticky top-0 shadow-sm">
    <div class="flex items-center justify-between px-8 py-6">
        <div class="flex items-center space-x-6">
            <button @click="sidebarOpen = true" class="lg:hidden p-2.5 text-purple-600 hover:bg-purple-100 rounded-xl transition-colors shadow-sm border border-purple-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ $pageTitle }}</h1>
                <p class="text-[11px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-0.5">{{ $pageSubtitle }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-6">
            <div class="hidden xl:flex items-center space-x-2 bg-white/80 backdrop-blur-sm px-4 py-2.5 rounded-2xl border-2 border-purple-200 shadow-sm">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-lg shadow-emerald-300"></div>
                <span class="text-xs font-bold text-slate-600">{{ now()->format('l, F j, Y') }}</span>
            </div>
            
            <div class="flex items-center space-x-3">
                <div class="relative group">
                    <x-notification-icon />
                </div>
                <div class="w-px h-8 bg-purple-200 mx-2"></div>
                <button class="p-2.5 bg-white hover:bg-purple-100 text-purple-600 hover:text-purple-700 rounded-2xl transition-all duration-300 border-2 border-purple-200 hover:border-purple-300 group relative shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 transform group-hover:rotate-45 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>
    </div>
</header>
