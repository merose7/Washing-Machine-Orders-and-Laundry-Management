<!DOCTYPE html>
<html>
<head>
    <title>The Daily Wash Notification</title>
</head>
<body>
    <h1>Notifikasi Booking </h1>
    <p>Dear {{ $data['name'] }},</p>
    <p>{{ $data['message'] }}</p>
    <p>Booking Time: {{ $data['booking_time'] }}</p>
    <p>Machine ID: {{ $data['machine_id'] }}</p>
    <p>Terima Kasih. Sudah Menggunakan Jasa Laundry The Daily Wash</p>
</body>
</html>
