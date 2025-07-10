<!DOCTYPE html>
<html>
<head>
    <title>The Daily Wash Notification</title>
</head>
<body>
    <h1>The Daily Wash Ketintang </h1>
    <p>Dear {{ $data['name'] }},</p>
    <p>{{ $data['message'] }}</p>
    <p>Booking Time: {{ $data['booking_time'] }}</p>
    <p>Machine ID: {{ $data['machine_id'] }}</p>
    <p>Payment Method: {{ ucfirst($data['payment_method']) }}</p>
    <p>Payment Status: {{ ucfirst($data['payment_status']) }}</p>
    <p> </p>
    <p>Terima Kasih. Sudah Menggunakan Jasa Laundry The Daily Wash</p>
</body>
</html>
