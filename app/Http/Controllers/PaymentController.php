<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanceReportExport;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request)
    {
        try {
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

            Log::info('Midtrans grossAmount: ' . $grossAmount);

            // Harga Machine 10000 
            if ($grossAmount < 10000) {
                $grossAmount = 10000;
            }

            $params = [
                'transaction_details' => [
                    'order_id' => 'BOOKING-' . $booking->id,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $booking->customer_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate snap token'], 500);
        }
    }

    public function index()
    {
        // Fetch payments separated by method with booking eager loaded
        $cashPayments = \App\Models\Payment::with('booking')
            ->whereRaw('LOWER(TRIM(payment_method)) = ?', ['cash'])
            ->whereIn('status', ['paid'])
            ->get();

        $midtransPayments = \App\Models\Payment::with('booking')
            ->whereRaw('LOWER(TRIM(payment_method)) = ?', ['midtrans'])
            ->whereIn('status', ['paid', 'pending', 'completed'])
            ->get();

        // Debug: Log midtrans payments count and amounts
        \Log::info('Midtrans payments count: ' . $midtransPayments->count());
        \Log::info('Midtrans payments total amount: ' . $midtransPayments->sum('amount'));

        // Calculate totals
        $totalCashPayments = $cashPayments->sum('amount');
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

        // Query payments joined with bookings, filter paid, group by month and payment method
        $query = \App\Models\Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m') as month,
            payments.payment_method,
            SUM(payments.amount) as total_amount
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid');

        if (!empty($selectedMonths)) {
            $query->whereIn(\DB::raw("DATE_FORMAT(bookings.booking_time, '%Y-%m')"), $selectedMonths);
        }

        $query->groupBy('month', 'payments.payment_method')
            ->orderBy('month', 'desc');

        $results = $query->get();

        // Transform results to structure: [month => [payment_method => total_amount]]
        $data = [];
        foreach ($results as $row) {
            $month = is_array($row) ? $row['month'] : $row->month;
            $paymentMethod = is_array($row) ? $row['payment_method'] : $row->payment_method;
            $totalAmount = is_array($row) ? $row['total_amount'] : $row->total_amount;
            $data[$month][$paymentMethod] = $totalAmount;
        }

        // Prepare data for view: list of months and totals per payment method
        $months = array_unique(array_map(function ($row) {
            return is_array($row) ? $row['month'] : $row->month;
        }, $results->toArray()));

        $reportData = [];
        foreach ($months as $month) {
            $reportData[] = [
                'month' => $month,
                'cash' => $data[$month]['cash'] ?? 0,
                'midtrans' => $data[$month]['midtrans'] ?? 0,
                'total' => ($data[$month]['cash'] ?? 0) + ($data[$month]['midtrans'] ?? 0),
            ];
        }

        return view('admin.finance_report', compact('reportData', 'months', 'selectedMonths'));
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
}
