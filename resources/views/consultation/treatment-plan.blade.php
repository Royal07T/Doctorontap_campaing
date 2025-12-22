<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Treatment Plan – DoctorOnTap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
</head>

<body class="bg-slate-50 min-h-screen text-gray-800">

<!-- HEADER -->
<header class="bg-white border-b sticky top-0 z-10 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center hover:opacity-80 transition-opacity">
            <img src="{{ asset('img/logo-text.png') }}" class="h-6 sm:h-8" alt="DoctorOnTap">
        </a>
        <div class="flex items-center gap-4">
            <span class="text-xs sm:text-sm text-gray-500 hidden sm:inline">Treatment Plan</span>
            <a href="{{ url('/') }}" class="text-sm sm:text-base font-semibold text-purple-600 hover:text-purple-700 transition-colors">
                Home
            </a>
        </div>
    </div>
</header>

<!-- MAIN -->
<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 sm:space-y-6">

    <!-- PAYMENT STATUS -->
    @if($consultation->isPaid())
    <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 text-xs sm:text-sm text-green-800">
        ✅ Payment confirmed. Your treatment plan is now available.
    </div>
    @endif

    <!-- HERO CARD -->
    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4">
            <div class="flex-1">
                <h1 class="text-xl sm:text-2xl font-semibold">Hello {{ $consultation->first_name }},</h1>
                <p class="text-sm sm:text-base text-gray-600 mt-1">
                    This is your personalized treatment plan prepared by
                    <strong>Dr. {{ $consultation->doctor->full_name }}</strong>
                </p>
            </div>
            <div class="text-xs sm:text-sm text-gray-600 mt-2 md:mt-0 md:text-right">
                <p><strong>Reference:</strong> <span class="font-mono text-xs">{{ $consultation->reference }}</span></p>
                <p><strong>Date:</strong> {{ $consultation->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- QUICK SUMMARY -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 sm:p-6">
        <h2 class="text-base sm:text-lg font-semibold text-blue-900 mb-2 sm:mb-3">What you should do</h2>
        <ul class="list-disc list-inside space-y-1 sm:space-y-2 text-xs sm:text-sm text-blue-800">
            <li>Read your treatment plan carefully</li>
            <li>Take medications exactly as prescribed</li>
            <li>Follow the lifestyle and follow-up instructions</li>
            <li>Contact us if anything is unclear</li>
        </ul>
    </div>

    <!-- CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        <!-- LEFT / MAIN -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">

            <!-- TREATMENT PLAN -->
            <section class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold mb-2 sm:mb-3 flex items-center gap-2">
                    🩺 Treatment Plan
                </h3>
                <div class="text-sm sm:text-base text-gray-700 leading-6 sm:leading-7 whitespace-pre-line break-words">
                    {{ $consultation->treatment_plan }}
                </div>
            </section>

            <!-- MEDICATIONS -->
            @if($consultation->prescribed_medications && count($consultation->prescribed_medications))
            <section class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 flex items-center gap-2">
                    💊 Prescribed Medications
                </h3>

                <div class="space-y-3 sm:space-y-4">
                    @foreach($consultation->prescribed_medications as $med)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 sm:p-4">
                        <h4 class="text-sm sm:text-base font-semibold text-purple-900">{{ $med['name'] }}</h4>
                        <ul class="text-xs sm:text-sm text-purple-800 mt-2 space-y-1">
                            <li><strong>Dosage:</strong> {{ $med['dosage'] }}</li>
                            <li><strong>Frequency:</strong> {{ $med['frequency'] }}</li>
                            <li><strong>Duration:</strong> {{ $med['duration'] }}</li>
                        </ul>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- FOLLOW UP -->
            @if($consultation->follow_up_instructions)
            <section class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold mb-2 sm:mb-3">📅 Follow-up Instructions</h3>
                <div class="text-sm sm:text-base text-gray-700 leading-6 sm:leading-7 whitespace-pre-line break-words">
                    {{ $consultation->follow_up_instructions }}
                </div>
            </section>
            @endif

            <!-- LIFESTYLE -->
            @if($consultation->lifestyle_recommendations)
            <section class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold mb-2 sm:mb-3">🌿 Lifestyle Recommendations</h3>
                <div class="text-sm sm:text-base text-gray-700 leading-6 sm:leading-7 whitespace-pre-line break-words">
                    {{ $consultation->lifestyle_recommendations }}
                </div>
            </section>
            @endif

        </div>

        <!-- RIGHT / SIDEBAR -->
        <aside class="space-y-4 sm:space-y-6 lg:sticky lg:top-6 lg:self-start">

            <!-- NEXT APPOINTMENT -->
            @if($consultation->next_appointment_date)
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 text-center">
                <h4 class="text-sm sm:text-base font-semibold mb-2">Next Appointment</h4>
                <p class="text-2xl sm:text-3xl font-bold text-blue-600">
                    {{ \Carbon\Carbon::parse($consultation->next_appointment_date)->format('M d') }}
                </p>
                <p class="text-xs sm:text-sm text-gray-600">
                    {{ \Carbon\Carbon::parse($consultation->next_appointment_date)->format('Y') }}
                </p>
            </div>
            @endif

            <!-- HELP -->
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h4 class="text-sm sm:text-base font-semibold mb-2">Need help?</h4>
                <p class="text-xs sm:text-sm text-gray-600 mb-3">
                    If you have any questions or concerns, contact us.
                </p>
                <p class="text-xs sm:text-sm break-words"><strong>Email:</strong> <a href="mailto:inquiries@doctorontap.com.ng" class="text-purple-600 hover:underline">inquiries@doctorontap.com.ng</a></p>
                <p class="text-xs sm:text-sm"><strong>Phone:</strong> <a href="tel:08177777122" class="text-purple-600 hover:underline">0817 777 7122</a></p>
            </div>

            <!-- REASSURANCE -->
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 sm:p-5 text-xs sm:text-sm text-green-800">
                💚 You're not alone. Your care team is here to support you.
            </div>

        </aside>
    </div>

    <!-- PRINT -->
    <div class="text-center pt-4 sm:pt-6">
        <button onclick="window.print()"
            class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-800 text-sm sm:text-base transition-colors">
            📄 Print for Hospital / Pharmacy
        </button>
        <p class="text-xs text-gray-500 mt-2 px-4">
            Recommended if you are visiting another healthcare provider
        </p>
    </div>

</main>

<!-- FOOTER -->
<footer class="border-t bg-white mt-6 sm:mt-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 sm:py-6 text-center text-xs sm:text-sm text-gray-500">
        © {{ date('Y') }} DoctorOnTap · Confidential Medical Information
    </div>
</footer>

<style>
@media print {
    button { display: none !important; }
    body { background: white; }
}
</style>

</body>
</html>
