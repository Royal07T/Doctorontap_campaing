<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarePlan;
use App\Models\DoctorPayment;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialHubController extends Controller
{
    /**
     * Financial Hub overview dashboard.
     */
    public function index()
    {
        // ── Revenue summary ──
        $totalRevenue      = Payment::where('status', 'success')->sum('amount');
        $monthlyRevenue    = Payment::where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        $pendingPayments   = Payment::where('status', 'pending')->sum('amount');
        $totalInvoices     = Invoice::count();
        $unpaidInvoices    = Invoice::where('status', '!=', 'paid')->sum('total_amount');

        // ── Care Plan revenue breakdown ──
        $carePlanRevenue = CarePlan::selectRaw("plan_type, COUNT(*) as count, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count")
            ->groupBy('plan_type')
            ->get()
            ->keyBy('plan_type');

        // ── Monthly revenue trend (last 6 months) ──
        $monthlyTrend = Payment::where('status', 'success')
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // ── Doctor payouts summary ──
        $totalPayouts      = DoctorPayment::where('status', 'paid')->sum('amount');
        $pendingPayouts    = DoctorPayment::where('status', 'pending')->sum('amount');

        // ── Recent transactions ──
        $recentPayments = Payment::orderByDesc('created_at')->limit(15)->get();

        // ── Revenue by source ──
        $revenueByMethod = Payment::where('status', 'success')
            ->selectRaw("payment_method, SUM(amount) as total, COUNT(*) as count")
            ->groupBy('payment_method')
            ->get();

        $stats = compact(
            'totalRevenue', 'monthlyRevenue', 'pendingPayments',
            'totalInvoices', 'unpaidInvoices',
            'totalPayouts', 'pendingPayouts'
        );

        return view('admin.financial-hub.index', compact(
            'stats', 'carePlanRevenue', 'monthlyTrend',
            'recentPayments', 'revenueByMethod'
        ));
    }

    /**
     * Invoices listing.
     */
    public function invoices(Request $request)
    {
        $query = Invoice::with('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%")
                  ->orWhere('customer_email', 'like', "%{$request->search}%");
            });
        }

        $invoices = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.financial-hub.invoices', compact('invoices'));
    }

    /**
     * Payments listing (ledger).
     */
    public function payments(Request $request)
    {
        $query = Payment::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%")
                  ->orWhere('customer_email', 'like', "%{$request->search}%");
            });
        }

        $payments = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.financial-hub.payments', compact('payments'));
    }

    /**
     * Doctor payouts listing.
     */
    public function payouts(Request $request)
    {
        $query = DoctorPayment::with('doctor');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.financial-hub.payouts', compact('payouts'));
    }
}
