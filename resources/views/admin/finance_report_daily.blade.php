@extends('layouts.admin')

@section('title', 'Finance Report Daily')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Laporan Keuangan - Pemasukan Booking Bulan {{ $selectedMonth }}</h1>

    <form method="GET" action="{{ route('admin.financeReport.daily') }}" class="mb-4">
        <label for="month" class="mr-2 font-semibold">Select Month:</label>
        <input type="month" id="month" name="month" value="{{ $selectedMonth }}" required>
        <button type="submit" class="btn btn-primary ml-2">View</button>
    </form>

    <table class="min-w-full bg-white border border-gray-300 mt-6">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b border-gray-300">Day</th>
                <th class="py-2 px-4 border-b border-gray-300">Cash Income</th>
                <th class="py-2 px-4 border-b border-gray-300">Midtrans Income</th>
                <th class="py-2 px-4 border-b border-gray-300">Total Income</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData as $row)
            <tr>
                <td class="py-2 px-4 border-b border-gray-300">{{ $row['day'] }}</td>
                <td class="py-2 px-4 border-b border-gray-300">Rp {{ number_format($row['cash'], 0, ',', '.') }}</td>
                <td class="py-2 px-4 border-b border-gray-300">Rp {{ number_format($row['midtrans'], 0, ',', '.') }}</td>
                <td class="py-2 px-4 border-b border-gray-300 font-bold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="py-4 text-center">No data available for selected month</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
