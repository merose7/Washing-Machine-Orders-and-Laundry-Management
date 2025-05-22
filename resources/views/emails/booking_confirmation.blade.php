<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation</title>
</head>
<body>
    <h1>Booking Confirmation</h1>
    <p>Dear {{ $name }},</p>
    <p>{{ $message }}</p>
    <p>Booking Time: {{ $booking_time }}</p>
    <p>Machine ID: {{ $machine_id }}</p>
    <p>Thank you for using our service.</p>
</body>
</html>
