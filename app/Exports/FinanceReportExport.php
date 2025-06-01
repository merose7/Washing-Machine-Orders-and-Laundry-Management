<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinanceReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $query = Payment::selectRaw("
            DATE_FORMAT(bookings.booking_time, '%Y-%m') as month,
            payment_method,
            SUM(amount) as total_amount
        ")
        ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
        ->where('payments.status', 'paid')
        ->groupBy('month', 'payment_method')
        ->orderBy('month', 'desc')
        ->get();

        $data = [];
        foreach ($query as $row) {
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

        return collect($reportData);
    }

    public function headings(): array
    {
        return [
            'Month',
            'Cash Income',
            'Midtrans Income',
            'Total Income',
        ];
    }
}
