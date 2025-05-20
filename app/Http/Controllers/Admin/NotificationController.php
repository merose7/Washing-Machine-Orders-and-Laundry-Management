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
}
