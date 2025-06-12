<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Config;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanceReportExport;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        // Fetch cash payments with status 'paid' with booking and machine eager loaded
        $cashPayments = \App\Models\Payment::with(['booking.machine'])
            ->where('payment_method', 'cash')
            ->where('status', 'paid')
            ->get();

        // Override each cash payment amount to 10,000
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
    // 'phone' => 'required|string', // Removed phone validation as per user request
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

            // Create or update Payment record with status 'pending'
            \App\Models\Payment::updateOrCreate(
                ['booking_id' => $booking->id, 'payment_method' => 'midtrans'],
                ['amount' => $grossAmount, 'status' => 'pending']
            );

            LogFacade::info('Midtrans Snap Token generated successfully: ' . $snapToken);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            LogFacade::error('Midtrans Snap Token Connection Error: ' . $e->getMessage());
            LogFacade::error('Midtrans Snap Token Connection Trace: ' . $e->getTraceAsString());
            LogFacade::error('Midtrans Snap Token Request Params on Error: ' . json_encode($params));
            LogFacade::error('Midtrans Snap Token Full Exception: ' . $e);
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
        $selectedMonths = $request->input('months', []);

        // Query cash payments count per month
        $cashQuery = \App\Models\Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m') as month,
            COUNT(payments.id) as cash_count
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->where('payments.payment_method', 'cash');

        if (!empty($selectedMonths)) {
            $cashQuery->whereIn(\DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), $selectedMonths);
        }

        $cashQuery->groupBy('month')
            ->orderBy('month', 'desc');

        $cashResults = $cashQuery->get();

        // Query midtrans payments sum per month
        $midtransQuery = \App\Models\Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m') as month,
            SUM(payments.amount) as midtrans_total
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->where('payments.payment_method', 'midtrans');

        if (!empty($selectedMonths)) {
            $midtransQuery->whereIn(\DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), $selectedMonths);
        }

        $midtransQuery->groupBy('month')
            ->orderBy('month', 'desc');

        $midtransResults = $midtransQuery->get();

        // Transform results to structure: [month => cash_count or midtrans_total]
        $cashData = [];
        foreach ($cashResults as $row) {
            $month = is_array($row) ? $row['month'] : $row->month;
            $cashCount = is_array($row) ? $row['cash_count'] : $row->cash_count;
            $cashData[$month] = $cashCount * 10000; // 10,000 per cash payment
        }

        $midtransData = [];
        foreach ($midtransResults as $row) {
            $month = is_array($row) ? $row['month'] : $row->month;
            $midtransTotal = is_array($row) ? $row['midtrans_total'] : $row->midtrans_total;
            $midtransData[$month] = $midtransTotal;
        }

        // Prepare data for view: list of months and totals per payment method
        $months = array_unique(array_merge(array_keys($cashData), array_keys($midtransData)));
        rsort($months);

        $reportData = [];
        foreach ($months as $month) {
            $cashIncome = $cashData[$month] ?? 0;
            $midtransIncome = $midtransData[$month] ?? 0;
            $reportData[] = [
                'month' => $month,
                'cash' => $cashIncome,
                'midtrans' => $midtransIncome,
                'total' => $cashIncome + $midtransIncome,
            ];
        }

        return view('admin.finance_report', compact('reportData', 'months', 'selectedMonths'));
    }

    public function financeReportDaily(Request $request)
    {
        $selectedMonth = $request->input('month'); // expected format 'YYYY-MM'

        if (!$selectedMonth) {
            return redirect()->back()->withErrors(['month' => 'Please select a month']);
        }

        // Query cash payments count per day in selected month
        $cashQuery = Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m-%d') as day,
            COUNT(payments.id) as cash_count
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

        // Transform results to structure: [day => cash_count or midtrans_total]
        $cashData = [];
        foreach ($cashResults as $row) {
            $day = $row->day;
            $cashData[$day] = $row->cash_count * 10000; // 10,000 per cash payment
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
        $reportData = $this->getFinanceReportData();

        $pdf = Pdf::loadView('admin.finance_report_pdf', compact('reportData'));
        return $pdf->download('finance_report.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new FinanceReportExport, 'finance_report.xlsx');
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

        // Extract booking id from order_id (assuming format BOOKING-{id})
        $bookingId = null;
        if (preg_match('/BOOKING-(\d+)/', $orderId, $matches)) {
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

            // Also update booking payment_status and machine status accordingly
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
