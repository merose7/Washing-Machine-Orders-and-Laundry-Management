<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanceReportExport;
use App\Exports\FinanceReportDailyExport;
use App\Models\Payment;

class PaymentController extends Controller
{
    /**
     * Generate monthly income report data.
     *
     * @param array $months Optional list of months to filter.
     * @return array
     */
    private function generateMonthlyIncomeReport(array $months = [])
    {
        $cashData = $this->calculateBookingRevenueByMonth('cash', $months);
        $midtransData = $this->calculateBookingRevenueByMonth('midtrans', $months);

        $allMonths = array_unique(array_merge(array_keys($cashData), array_keys($midtransData)));
        rsort($allMonths);

        $reportData = [];
        foreach ($allMonths as $month) {
            $cashIncome = $cashData[$month] ?? 0;
            $midtransIncome = $midtransData[$month] ?? 0;
            $reportData[] = [
                'month' => $month,
                'cash' => $cashIncome,
                'midtrans' => $midtransIncome,
                'total' => $cashIncome + $midtransIncome,
            ];
        }

        return $reportData;
    }

    /**
     * Calculate booking revenue by payment method grouped by month.
     *
     * @param string $paymentMethod
     * @param array $months Optional list of months to filter.
     * @return array
     */
    private function calculateBookingRevenueByMonth(string $paymentMethod, array $months = [])
    {
        $query = \App\Models\Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m') as month,
            SUM(payments.amount) as total_amount
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->where('payments.payment_method', $paymentMethod);

        if (!empty($months)) {
            $query->whereIn(\DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), $months);
        }

        $query->groupBy(\DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"))
            ->orderBy(\DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), 'desc');

        $results = $query->get();

        $data = [];
        foreach ($results as $row) {
            $data[$row->month] = $row->total_amount;
        }

        return $data;
    }

    /**
     * Get financial summary by month.
     *
     * @param array $months Optional list of months to filter.
     * @return array
     */
    private function getFinancialSummaryByMonth(array $months = [])
    {
        $reportData = $this->generateMonthlyIncomeReport($months);

        $summary = [
            'totalCash' => 0,
            'totalMidtrans' => 0,
            'totalIncome' => 0,
        ];

        foreach ($reportData as $data) {
            $summary['totalCash'] += $data['cash'];
            $summary['totalMidtrans'] += $data['midtrans'];
            $summary['totalIncome'] += $data['total'];
        }

        return $summary;
    }
    public function index()
    {
        $cashPayments = \App\Models\Payment::with(['booking.machine'])
            ->where('payment_method', 'cash')
            ->where('status', 'paid')
            ->get();

        $cashPayments->each(function ($payment) {
            $payment->amount = 10000;
        });

        // Recalculate total cash payments as 10,000 times the number of cash payments
        $totalCashPayments = $cashPayments->count() * 10000;

        // Fetch payments with payment_method 'midtrans' (all statuses)
        $midtransPayments = \App\Models\Payment::with('booking')
            ->whereRaw('LOWER(TRIM(payment_method)) = ?', ['midtrans'])
            ->get();

        // Fetch recent bookings with related machine and payment
        $recentBookings = \App\Models\Booking::with(['machine', 'payment'])
            ->orderBy('booking_time', 'desc')
            ->limit(10)
            ->get();


        // Debug: Log cash payments count and total amount
        \Log::info('Cash payments count: ' . $cashPayments->count());
        \Log::info('Total cash payments recalculated as 10,000 per payment: ' . $totalCashPayments);

        // Debug: Log midtrans payments count and amounts
        \Log::info('Midtrans payments count: ' . $midtransPayments->count());
        \Log::info('Midtrans payments total amount: ' . $midtransPayments->sum('amount'));

        // Debug: Log recent bookings count
        \Log::info('Recent bookings count: ' . $recentBookings->count());

        // Calculate total midtrans payments
        $totalMidtransPayments = $midtransPayments->sum('amount');
        $totalPayments = $totalCashPayments + $totalMidtransPayments;

        return view('admin.payments', compact(
            'cashPayments',
            'midtransPayments',
            'totalCashPayments',
            'totalMidtransPayments',
            'totalPayments'
        ));
    }

public function createSnapToken(Request $request)
{
    LogFacade::info('Entered createSnapToken method with request data: ' . json_encode($request->all()));

    try {
$validator = \Validator::make($request->all(), [
    'booking_id' => 'required|integer|exists:bookings,id',
    'email' => 'required|email',
    'phone' => 'nullable|string', 
]);

if ($validator->fails()) {
    LogFacade::error('Validation errors in createSnapToken: ' . json_encode($validator->errors()->all()));
    return response()->json(['error' => 'Validation failed', 'messages' => $validator->errors()->all()], 422);
}

        $validated = $validator->validated();

        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$is3ds = config('midtrans.is_3ds');
        Config::$isSanitized = config('midtrans.is_sanitized');

        $bookingId = $request->input('booking_id');
        $booking = \App\Models\Booking::with('machine')->find($bookingId);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $grossAmount = $booking->machine && $booking->machine->price > 0 ? $booking->machine->price : 10000;

        LogFacade::info('Midtrans grossAmount: ' . $grossAmount);

        // Harga Machine 10000 
        if ($grossAmount < 10000) {
            $grossAmount = 10000;
        }

$params = [
    'transaction_details' => [
        'order_id' => 'BOOKING-' . $booking->id . '-' . time(),
        'gross_amount' => $grossAmount,
    ],
    'customer_details' => [
        'first_name' => $booking->customer_name,
        'email' => $request->email,
        'phone' => $request->phone ?? '',
    ],
];

        LogFacade::info('Midtrans Snap Token Request Params: ' . json_encode($params));

        try {
            $snapToken = Snap::getSnapToken($params);

            // Create or update Payment record with status 'pending' and save order_id
            \App\Models\Payment::updateOrCreate(
                ['booking_id' => $booking->id, 'payment_method' => 'midtrans'],
                ['amount' => $grossAmount, 'status' => 'pending', 'order_id' => $params['transaction_details']['order_id']]
            );

            LogFacade::info('Midtrans Snap Token generated successfully: ' . $snapToken);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            LogFacade::error('Midtrans Snap Token Connection Error: ' . $e->getMessage());
            LogFacade::error('Midtrans Snap Token Connection Trace: ' . $e->getTraceAsString());
            LogFacade::error('Midtrans Snap Token Request Params on Error: ' . json_encode($params));
            LogFacade::error('Midtrans Snap Token Full Exception: ' . $e);
            LogFacade::error('Midtrans Snap Token Exception Class: ' . get_class($e));
            LogFacade::error('Midtrans Snap Token Exception File: ' . $e->getFile());
            LogFacade::error('Midtrans Snap Token Exception Line: ' . $e->getLine());
            return response()->json(['error' => 'Failed to generate snap token due to connection error', 'message' => $e->getMessage()], 500);
        }
    } catch (\Exception $e) {
        LogFacade::error('Midtrans Snap Token Error: ' . $e->getMessage());
        LogFacade::error('Midtrans Snap Token Trace: ' . $e->getTraceAsString());
        return response()->json(['error' => 'Failed to generate snap token', 'message' => $e->getMessage()], 500);
    }
}

public function getTotals()
    {
        $totalCashPayments = \App\Models\Payment::where('payment_method', 'cash')
            ->where('status', 'paid')
            ->sum('amount');
        $totalMidtransPayments = \App\Models\Payment::where('payment_method', 'midtrans')
            ->whereIn('status', ['paid', 'pending', 'completed'])
            ->sum('amount');
        $totalPayments = $totalCashPayments + $totalMidtransPayments;

        return response()->json([
            'totalCashPayments' => $totalCashPayments,
            'totalMidtransPayments' => $totalMidtransPayments,
            'totalPayments' => $totalPayments,
        ]);
    }

    public function getMidtransPayments()
    {
        $midtransPayments = \App\Models\Payment::with('booking')->where('payment_method', 'midtrans')
            ->whereIn('status', ['paid', 'pending', 'completed'])
            ->get();

        return response()->json($midtransPayments);
    }

public function financeReport(Request $request)
{
    $filterDate = $request->input('filter_date');
    $filterMonth = $request->input('filter_month');
    $filterYear = $request->input('filter_year');

    $query = \App\Models\Payment::query()
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid');

    if ($filterDate) {
        $query->whereDate('bookings.booking_time', $filterDate);
    } elseif ($filterMonth) {
        $query->whereRaw("DATE_FORMAT(bookings.booking_time, '%Y-%m') = ?", [$filterMonth]);
    } elseif ($filterYear) {
        $query->whereRaw("DATE_FORMAT(bookings.booking_time, '%Y') = ?", [$filterYear]);
    }

    // Group by month and payment method for report data
    $results = $query->selectRaw("
        DATE_FORMAT(bookings.booking_time, '%Y-%m') as month,
        payments.payment_method,
        SUM(payments.amount) as total_amount
    ")
    ->groupBy('month', 'payments.payment_method')
    ->orderBy('month', 'desc')
    ->get();

    $data = [];
    foreach ($results as $row) {
        $data[$row->month][$row->payment_method] = $row->total_amount;
    }

    $months = array_keys($data);
    $reportData = [];
    foreach ($months as $month) {
        $reportData[] = [
            'month' => $month,
            'cash' => $data[$month]['cash'] ?? 0,
            'midtrans' => $data[$month]['midtrans'] ?? 0,
            'total' => ($data[$month]['cash'] ?? 0) + ($data[$month]['midtrans'] ?? 0),
        ];
    }

    $financialSummary = [
        'totalCash' => array_sum(array_column($reportData, 'cash')),
        'totalMidtrans' => array_sum(array_column($reportData, 'midtrans')),
        'totalIncome' => array_sum(array_column($reportData, 'total')),
    ];

    // Calculate totals for view synchronization using same logic as payments index
    $totalCashPayments = \App\Models\Payment::where('payment_method', 'cash')
        ->where('status', 'paid')
        ->when($filterDate, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereDate('booking_time', $filterDate)))
        ->when($filterMonth, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y-%m') = ?", [$filterMonth])))
        ->when($filterYear, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y') = ?", [$filterYear])))
        ->sum('amount');

    $totalMidtransPayments = \App\Models\Payment::where('payment_method', 'midtrans')
        ->whereIn('status', ['paid', 'pending', 'completed'])
        ->when($filterDate, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereDate('booking_time', $filterDate)))
        ->when($filterMonth, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y-%m') = ?", [$filterMonth])))
        ->when($filterYear, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y') = ?", [$filterYear])))
        ->sum('amount');

    $totalPayments = $totalCashPayments + $totalMidtransPayments;

    $totalTransactions = \App\Models\Payment::where('status', 'paid')
        ->when($filterDate, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereDate('booking_time', $filterDate)))
        ->when($filterMonth, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y-%m') = ?", [$filterMonth])))
        ->when($filterYear, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y') = ?", [$filterYear])))
        ->count();

    // Fetch detailed payments for cash and midtrans
    $cashPayments = \App\Models\Payment::with(['booking.machine'])
        ->where('payment_method', 'cash')
        ->where('status', 'paid')
        ->when($filterDate, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereDate('booking_time', $filterDate)))
        ->when($filterMonth, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y-%m') = ?", [$filterMonth])))
        ->when($filterYear, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y') = ?", [$filterYear])))
        ->get();

    $midtransPayments = \App\Models\Payment::with(['booking.machine'])
        ->where('payment_method', 'midtrans')
        ->whereIn('status', ['paid', 'pending', 'completed'])
        ->when($filterDate, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereDate('booking_time', $filterDate)))
        ->when($filterMonth, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y-%m') = ?", [$filterMonth])))
        ->when($filterYear, fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereRaw("DATE_FORMAT(booking_time, '%Y') = ?", [$filterYear])))
        ->get();

    return view('admin.finance_report', compact(
        'reportData',
        'months',
        'financialSummary',
        'totalPayments',
        'totalCashPayments',
        'totalMidtransPayments',
        'totalTransactions',
        'cashPayments',
        'midtransPayments',
        'filterDate',
        'filterMonth',
        'filterYear'
    ));
}

    public function financeReportDaily(Request $request)
    {
        $selectedMonth = $request->input('month'); // expected format 'YYYY-MM'

        if (!$selectedMonth) {
            return redirect()->back()->withErrors(['month' => 'Please select a month']);
        }

        // Query cash payments sum per day in selected month
        $cashQuery = Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m-%d') as day,
            SUM(payments.amount) as cash_total
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->where('payments.payment_method', 'cash')
        ->where(DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), $selectedMonth)
        ->groupBy('day')
        ->orderBy('day', 'asc');

        $cashResults = $cashQuery->get();

        // Query midtrans payments sum per day in selected month
        $midtransQuery = Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m-%d') as day,
            SUM(payments.amount) as midtrans_total
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->where('payments.payment_method', 'midtrans')
        ->where(DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), $selectedMonth)
        ->groupBy('day')
        ->orderBy('day', 'asc');

        $midtransResults = $midtransQuery->get();

        // Transform results to structure: [day => cash_total or midtrans_total]
        $cashData = [];
        foreach ($cashResults as $row) {
            $day = $row->day;
            $cashData[$day] = $row->cash_total;
        }

        $midtransData = [];
        foreach ($midtransResults as $row) {
            $day = $row->day;
            $midtransData[$day] = $row->midtrans_total;
        }

        // Prepare data for view: list of days and totals per payment method
        $days = array_unique(array_merge(array_keys($cashData), array_keys($midtransData)));
        sort($days);

        $reportData = [];
        foreach ($days as $day) {
            $cashIncome = $cashData[$day] ?? 0;
            $midtransIncome = $midtransData[$day] ?? 0;
            $reportData[] = [
                'day' => $day,
                'cash' => $cashIncome,
                'midtrans' => $midtransIncome,
                'total' => $cashIncome + $midtransIncome,
            ];
        }

        return view('admin.finance_report_daily', compact('reportData', 'days', 'selectedMonth'));
    }

public function exportPdf()
{
    $filterDate = request()->input('filter_date');
    $filterMonth = request()->input('filter_month');
    $filterYear = request()->input('filter_year');

    // Fetch cash payments and override amount to 10000 per payment as in index method
    $cashPaymentsQuery = \App\Models\Payment::with(['booking.machine'])
        ->where('payment_method', 'cash')
        ->where('status', 'paid');

    if ($filterDate) {
        $cashPaymentsQuery->whereHas('booking', fn($q) => $q->whereDate('booking_time', $filterDate));
    } elseif ($filterMonth) {
        $cashPaymentsQuery->whereHas('booking', fn($q) => $q->whereRaw("DATE_FORMAT(booking_time, '%Y-%m') = ?", [$filterMonth]));
    } elseif ($filterYear) {
        $cashPaymentsQuery->whereHas('booking', fn($q) => $q->whereRaw("DATE_FORMAT(booking_time, '%Y') = ?", [$filterYear]));
    }

    $cashPayments = $cashPaymentsQuery->get();

    $cashPayments->each(function ($payment) {
        $payment->amount = 10000;
    });

    // Calculate total cash payments as count * 10000
    $totalCashPayments = $cashPayments->count() * 10000;

    // Build cashData grouped by month with fixed amount 10000 per payment
    $cashData = [];
    foreach ($cashPayments as $payment) {
        $month = null;
        if ($payment->booking) {
            if ($payment->booking->booking_time instanceof \Illuminate\Support\Carbon) {
                $month = $payment->booking->booking_time->format('Y-m');
            } else {
                $month = date('Y-m', strtotime($payment->booking->booking_time));
            }
        }
        if ($month) {
            if (!isset($cashData[$month])) {
                $cashData[$month] = 0;
            }
            $cashData[$month] += 10000;
        }
    }

    // Fetch midtrans data using existing method with filters
    $monthsFilter = [];
    if ($filterMonth) {
        $monthsFilter[] = $filterMonth;
    } elseif ($filterYear) {
        // If year filter is set, get all months in that year
        $monthsFilter = \App\Models\Payment::selectRaw("DISTINCT DATE_FORMAT(bookings.booking_time, '%Y-%m') as month")
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->where('payments.status', 'paid')
            ->whereRaw("DATE_FORMAT(bookings.booking_time, '%Y') = ?", [$filterYear])
            ->pluck('month')
            ->toArray();
    }

    $midtransData = $this->calculateBookingRevenueByMonth('midtrans', $monthsFilter);

    // Merge months from both cash and midtrans
    $allMonths = array_unique(array_merge(array_keys($cashData), array_keys($midtransData)));
    rsort($allMonths);

    // Build reportData array
    $reportData = [];
    foreach ($allMonths as $month) {
        $cashIncome = $cashData[$month] ?? 0;
        $midtransIncome = $midtransData[$month] ?? 0;
        $reportData[] = [
            'month' => $month,
            'cash' => $cashIncome,
            'midtrans' => $midtransIncome,
            'total' => $cashIncome + $midtransIncome,
        ];
    }

    // Calculate total midtrans payments sum
    $totalMidtransPayments = array_sum($midtransData);

    $totalPayments = $totalCashPayments + $totalMidtransPayments;

    // Fetch detailed midtrans payments with filters
    $midtransPaymentsQuery = \App\Models\Payment::with(['booking.machine'])
        ->where('payment_method', 'midtrans')
        ->whereIn('status', ['paid', 'pending', 'completed']);

    if ($filterDate) {
        $midtransPaymentsQuery->whereHas('booking', fn($q) => $q->whereDate('booking_time', $filterDate));
    } elseif ($filterMonth) {
        $midtransPaymentsQuery->whereHas('booking', fn($q) => $q->whereRaw("DATE_FORMAT(booking_time, '%Y-%m') = ?", [$filterMonth]));
    } elseif ($filterYear) {
        $midtransPaymentsQuery->whereHas('booking', fn($q) => $q->whereRaw("DATE_FORMAT(booking_time, '%Y') = ?", [$filterYear]));
    }

    $midtransPayments = $midtransPaymentsQuery->get();

    $pdf = Pdf::loadView('admin.finance_report_pdf', compact('reportData', 'totalCashPayments', 'totalMidtransPayments', 'totalPayments', 'cashPayments', 'midtransPayments'));
    return $pdf->stream('finance_report.pdf'); // Changed from download to stream to stay on page
}

    public function exportPdfDaily(Request $request)
    {
        $selectedMonth = $request->input('month');
        if (!$selectedMonth) {
            return redirect()->back()->withErrors(['month' => 'Please select a month']);
        }

        // Generate daily report data for the selected month
        $cashQuery = \App\Models\Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m-%d') as day,
            SUM(payments.amount) as cash_total
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->where('payments.payment_method', 'cash')
        ->where(\DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), $selectedMonth)
        ->groupBy('day')
        ->orderBy('day', 'asc')
        ->get();

        $midtransQuery = \App\Models\Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m-%d') as day,
            SUM(payments.amount) as midtrans_total
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->where('payments.payment_method', 'midtrans')
        ->where(\DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), $selectedMonth)
        ->groupBy('day')
        ->orderBy('day', 'asc')
        ->get();

        $cashData = [];
        foreach ($cashQuery as $row) {
            $cashData[$row->day] = $row->cash_total;
        }

        $midtransData = [];
        foreach ($midtransQuery as $row) {
            $midtransData[$row->day] = $row->midtrans_total;
        }

        $days = array_unique(array_merge(array_keys($cashData), array_keys($midtransData)));
        sort($days);

        $reportData = [];
        foreach ($days as $day) {
            $cashIncome = $cashData[$day] ?? 0;
            $midtransIncome = $midtransData[$day] ?? 0;
            $reportData[] = [
                'day' => $day,
                'cash' => $cashIncome,
                'midtrans' => $midtransIncome,
                'total' => $cashIncome + $midtransIncome,
            ];
        }

        $pdf = Pdf::loadView('admin.finance_report_daily_pdf', compact('reportData', 'selectedMonth'));
        return $pdf->download("finance_report_daily_{$selectedMonth}.pdf");
    }

    public function exportExcel()
    {
        return Excel::download(new FinanceReportExport, 'finance_report.xlsx');
    }

    public function exportExcelDaily(Request $request)
    {
        $selectedMonth = $request->input('month');
        if (!$selectedMonth) {
            return redirect()->back()->withErrors(['month' => 'Please select a month']);
        }

        return Excel::download(new \App\Exports\FinanceReportDailyExport($selectedMonth), "finance_report_daily_{$selectedMonth}.xlsx");
    }

    private function getFinanceReportData()
    {
        $query = \App\Models\Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m') as month,
            payments.payment_method,
            SUM(amount) as total_amount
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->groupBy('month', 'payments.payment_method')
        ->orderBy('month', 'desc');

        $results = $query->get();

        $data = [];
        foreach ($results as $row) {
            $data[$row->month][$row->payment_method] = $row->total_amount;
        }

        $months = array_keys($data);
        $reportData = [];
        foreach ($months as $month) {
            $reportData[] = [
                'month' => $month,
                'cash' => $data[$month]['cash'] ?? 0,
                'midtrans' => $data[$month]['midtrans'] ?? 0,
                'total' => ($data[$month]['cash'] ?? 0) + ($data[$month]['midtrans'] ?? 0),
            ];
        }

        return $reportData;
    }

    public function handleWebhook(Request $request)
    {
        LogFacade::info('Midtrans webhook received: ' . $request->getContent());

        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$is3ds = config('midtrans.is_3ds');
        Config::$isSanitized = config('midtrans.is_sanitized');

        $payload = $request->getContent();
        $notification = json_decode($payload);

        // Verify signature key
        $orderId = $notification->order_id ?? null;
        $statusCode = $notification->status_code ?? null;
        $grossAmount = $notification->gross_amount ?? null;
        $signatureKey = $notification->signature_key ?? null;
        $transactionStatus = $notification->transaction_status ?? null;
        $fraudStatus = $notification->fraud_status ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            LogFacade::warning('Midtrans webhook missing required fields');
            return response('Bad Request', 400);
        }

        $serverKey = config('midtrans.server_key');
        $input = $orderId . $statusCode . $grossAmount . $serverKey;
        $expectedSignature = hash('sha512', $input);

        if ($signatureKey !== $expectedSignature) {
            LogFacade::warning('Midtrans webhook invalid signature');
            return response('Invalid signature', 403);
        }

        // Extract booking ID from order ID including payment_notif_test format
        $bookingId = null;
        if (preg_match('/BOOKING-(\d+)-\d+/', $orderId, $matches)) {
            $bookingId = $matches[1];
        } elseif (preg_match('/payment_notif_test_[^_]+_(\d+)/i', $orderId, $matches)) {
            $bookingId = $matches[1];
        } elseif (preg_match('/payment_notif_test_[^_]+_([a-z0-9\-]+)/i', $orderId, $matches)) {
            $bookingId = $matches[1];
        }

        if (!$bookingId) {
            LogFacade::warning('Midtrans webhook invalid order_id format: ' . $orderId);
            return response('Invalid order_id', 400);
        }

        // Find payment record with booking_id and payment_method 'midtrans'
        $payment = Payment::where('booking_id', $bookingId)
            ->where('payment_method', 'midtrans')
            ->first();

        if (!$payment) {
            LogFacade::warning('Midtrans webhook payment not found for booking_id: ' . $bookingId);
            return response('Payment not found', 404);
        }

        LogFacade::info("Midtrans webhook transaction_status: {$transactionStatus}, current payment status: {$payment->status}");

        // Map Midtrans transaction status to local payment status
        $statusMap = [
            'capture' => 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'unpaid',
            'expire' => 'unpaid',
            'cancel' => 'unpaid',
        ];

        $newStatus = $statusMap[$transactionStatus] ?? 'unpaid';

        if ($payment->status !== $newStatus) {
            LogFacade::info("Updating payment status from {$payment->status} to {$newStatus} for booking_id {$bookingId}");
            $payment->status = $newStatus;
            $payment->save();

            LogFacade::info("Midtrans payment status updated for booking_id {$bookingId} to {$newStatus}");

            $booking = $payment->booking;
            if ($booking) {
                $booking->payment_status = $newStatus;
                $booking->save();

                if (in_array($newStatus, ['paid', 'success'])) {
                    $machine = $booking->machine;
                    if ($machine) {
                        $machine->status = 'available';
                        $machine->booking_ends_at = null;
                        $machine->save();
                    }
                }
            }
        } else {
            LogFacade::info("Payment status for booking_id {$bookingId} is already {$payment->status}, no update needed.");
        }

        return response('OK', 200);
    }
}
