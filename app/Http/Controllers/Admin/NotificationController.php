<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
         $query = Notification::query();

        // Filter berdasarkan pencarian pesan
        if ($request->has('search') && $request->search != '') {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan tanggal
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('created_at', $request->date);
        }

        // Filter berdasarkan payment_method
        if ($request->has('payment_method') && $request->payment_method != '') {
            $query->where('payment_method', $request->payment_method);
        }

        $notifications = $query->where('payment_method', '!=', 'cash')->latest()->paginate(10);
        return view('admin.Notification.gmail_notification', compact('notifications'));
    }

    public function cashNotifications(Request $request)
    {
        $query = Notification::where('payment_method', 'cash');

        // Filter berdasarkan pencarian pesan
        if ($request->has('search') && $request->search != '') {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan tanggal
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('created_at', $request->date);
        }

        $notifications = $query->latest()->paginate(10);

        // Mark notifications as read
        $notificationIds = $notifications->pluck('id')->toArray();
        Notification::whereIn('id', $notificationIds)->update(['is_read' => true]);

        return view('admin.Notification.cash', compact('notifications'));
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function editCashPaymentStatus(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        // Extract booking ID from notification message
        preg_match('/Booking ID (\d+)/', $notification->message, $matches);
        $bookingId = $matches[1] ?? null;

        if (!$bookingId) {
            return redirect()->back()->with('error', 'Booking ID tidak ditemukan dalam notifikasi.');
        }

        $booking = \App\Models\Booking::find($bookingId);
        if (!$booking) {
            return redirect()->back()->with('error', 'Booking tidak ditemukan.');
        }

        // Toggle payment status based on input or current status
        $newStatus = $request->input('payment_status');
        if (!in_array($newStatus, ['paid', 'unpaid'])) {
            return redirect()->back()->with('error', 'Status pembayaran tidak valid.');
        }

        $booking->payment_status = $newStatus === 'paid' ? 'paid' : 'pending';
        $booking->save();

        // Update or create Payment record accordingly
        if ($newStatus === 'paid') {
            $amount = $booking->machine ? $booking->machine->price : 10000;
            \App\Models\Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'amount' => $amount,
                    'status' => 'paid',
                    'payment_method' => 'cash',
                ]
            );
        } else {
            // If unpaid, delete payment record if exists
            \App\Models\Payment::where('booking_id', $booking->id)->delete();
        }

        return redirect()->back()->with('success', 'Status pembayaran cash berhasil diperbarui.');
    }
}
