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
    $notifications = Notification::latest()->paginate(10);
    return view('admin.Notification.index', compact('notifications'));
}

}
