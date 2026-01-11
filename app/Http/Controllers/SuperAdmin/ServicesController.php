<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicesController extends Controller
{
    /**
     * Display services monitoring dashboard
     * 
     * All data is fetched in real-time directly from the database.
     * No caching is used to ensure the most up-to-date information.
     */
    public function index(Request $request)
    {
        $serviceType = $request->get('type', 'all'); // all, sms, email, payment
        $status = $request->get('status', 'all'); // all, success, failed
        $dateRange = $request->get('date_range', 'today'); // today, week, month, all

        // Calculate date range
        $startDate = match($dateRange) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'all' => null,
            default => now()->startOfDay(),
        };

        // Statistics - Real-time from database
        $stats = $this->getStatistics($startDate);

        // SMS Notifications - Real-time from database
        $smsData = $this->getSmsData($status, $startDate, $request);

        // Email Notifications - Real-time from database
        $emailData = $this->getEmailData($status, $startDate, $request);

        // Payments - Real-time from database
        $paymentData = $this->getPaymentData($status, $startDate, $request);

        return view('super-admin.services.index', compact(
            'stats',
            'smsData',
            'emailData',
            'paymentData',
            'serviceType',
            'status',
            'dateRange'
        ));
    }

    /**
     * Get overall statistics
     * 
     * All statistics are calculated in real-time from the database.
     * No caching - ensures fresh data on every request.
     */
    private function getStatistics($startDate = null): array
    {
        // Fresh database queries - no caching
        $smsQuery = NotificationLog::where('type', 'sms');
        $emailQuery = NotificationLog::where('type', 'email');
        $paymentQuery = Payment::query();

        if ($startDate) {
            $smsQuery->where('created_at', '>=', $startDate);
            $emailQuery->where('created_at', '>=', $startDate);
            $paymentQuery->where('created_at', '>=', $startDate);
        }

        // Real-time counts from database
        return [
            'sms' => [
                'total' => (clone $smsQuery)->count(),
                'success' => (clone $smsQuery)->whereIn('status', ['sent', 'delivered'])->count(),
                'failed' => (clone $smsQuery)->where('status', 'failed')->count(),
                'pending' => (clone $smsQuery)->where('status', 'pending')->count(),
            ],
            'email' => [
                'total' => (clone $emailQuery)->count(),
                'success' => (clone $emailQuery)->whereIn('status', ['sent', 'delivered'])->count(),
                'failed' => (clone $emailQuery)->where('status', 'failed')->count(),
                'pending' => (clone $emailQuery)->where('status', 'pending')->count(),
            ],
            'payment' => [
                'total' => (clone $paymentQuery)->count(),
                'success' => (clone $paymentQuery)->where('status', 'success')->count(),
                'failed' => (clone $paymentQuery)->where('status', 'failed')->count(),
                'pending' => (clone $paymentQuery)->where('status', 'pending')->count(),
            ],
        ];
    }

    /**
     * Get SMS data
     * 
     * Real-time query from notification_logs table.
     * No caching - fresh data on every request.
     */
    private function getSmsData(string $status, $startDate, Request $request): array
    {
        // Fresh database query
        $query = NotificationLog::where('type', 'sms')
            ->with('consultation')
            ->latest();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($status === 'success') {
            $query->whereIn('status', ['sent', 'delivered']);
        } elseif ($status === 'failed') {
            $query->where('status', 'failed');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('consultation_reference', 'like', "%{$search}%");
            });
        }

        return [
            'logs' => $query->paginate(20, ['*'], 'sms_page'),
            'stats' => [
                'total' => NotificationLog::where('type', 'sms')->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
                'success' => NotificationLog::where('type', 'sms')->whereIn('status', ['sent', 'delivered'])->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
                'failed' => NotificationLog::where('type', 'sms')->where('status', 'failed')->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
            ],
        ];
    }

    /**
     * Get Email data
     * 
     * Real-time query from notification_logs table.
     * No caching - fresh data on every request.
     */
    private function getEmailData(string $status, $startDate, Request $request): array
    {
        // Fresh database query
        $query = NotificationLog::where('type', 'email')
            ->with('consultation')
            ->latest();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($status === 'success') {
            $query->whereIn('status', ['sent', 'delivered']);
        } elseif ($status === 'failed') {
            $query->where('status', 'failed');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('consultation_reference', 'like', "%{$search}%");
            });
        }

        return [
            'logs' => $query->paginate(20, ['*'], 'email_page'),
            'stats' => [
                'total' => NotificationLog::where('type', 'email')->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
                'success' => NotificationLog::where('type', 'email')->whereIn('status', ['sent', 'delivered'])->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
                'failed' => NotificationLog::where('type', 'email')->where('status', 'failed')->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
            ],
        ];
    }

    /**
     * Get Payment data
     * 
     * Real-time query from payments table.
     * No caching - fresh data on every request.
     */
    private function getPaymentData(string $status, $startDate, Request $request): array
    {
        // Fresh database query
        $query = Payment::with('doctor')->latest();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($status === 'success') {
            $query->where('status', 'success');
        } elseif ($status === 'failed') {
            $query->where('status', 'failed');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        return [
            'payments' => $query->paginate(20, ['*'], 'payment_page'),
            'stats' => [
                'total' => Payment::when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
                'success' => Payment::where('status', 'success')->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
                'failed' => Payment::where('status', 'failed')->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))->count(),
            ],
        ];
    }
}
