<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trip Ticket — REQ-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#1b2a4a; --primary:#4e73df; --muted:#6b7280; --border:#dde3ef; }
        * { box-sizing: border-box; }
        body { margin:0; background:#eef2f8; color:#1f2937; font-family:Nunito,Arial,sans-serif; }
        .toolbar { max-width:820px; margin:24px auto 12px; display:flex; justify-content:space-between; gap:12px; }
        .toolbar a, .toolbar button { border:0; border-radius:9px; padding:10px 16px; font:700 14px Nunito,Arial,sans-serif; cursor:pointer; text-decoration:none; }
        .toolbar a { background:#fff; color:#334155; border:1px solid var(--border); }
        .toolbar button { background:var(--primary); color:#fff; }
        .ticket { width:min(820px, calc(100% - 32px)); margin:0 auto 32px; overflow:hidden; border-radius:16px; background:#fff; box-shadow:0 12px 32px rgba(31,41,55,.13); }
        .ticket-header { padding:28px 34px; color:#fff; background:linear-gradient(135deg,var(--navy),#334d84); display:flex; justify-content:space-between; gap:16px; }
        .brand { font-size:24px; font-weight:800; letter-spacing:.2px; }
        .subtitle { margin-top:4px; font-size:13px; opacity:.82; }
        .ticket-number { text-align:right; font-size:13px; opacity:.88; }
        .ticket-number strong { display:block; margin-top:3px; color:#fff; font-size:19px; letter-spacing:1px; }
        .ticket-body { padding:30px 34px 16px; }
        .status { display:inline-block; color:#166534; background:#dcfce7; border:1px solid #bbf7d0; border-radius:99px; font-size:12px; font-weight:800; padding:5px 12px; }
        .route { margin:20px 0 25px; padding:20px; border:1px dashed #aebbd4; border-radius:12px; background:#f9fbff; text-align:center; }
        .route .location { font-size:16px; font-weight:800; }
        .route .arrow { color:var(--primary); padding:8px 0; font-size:22px; font-weight:800; }
        .grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:0; border-top:1px solid var(--border); border-left:1px solid var(--border); }
        .item { min-height:79px; padding:14px 16px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); }
        .label { color:var(--muted); font-size:10px; font-weight:800; letter-spacing:.09em; text-transform:uppercase; }
        .value { margin-top:5px; font-size:15px; font-weight:700; line-height:1.35; }
        .notice { margin:18px 0 6px; padding:12px 14px; border-left:4px solid var(--primary); color:#3b4d71; background:#eef3ff; border-radius:6px; font-size:12px; }
        .ticket-footer { padding:16px 34px 23px; border-top:1px solid var(--border); color:var(--muted); font-size:11px; display:flex; justify-content:space-between; gap:16px; }
        @media (max-width:560px) { .ticket-header,.ticket-body,.ticket-footer{padding-left:20px;padding-right:20px}.ticket-header{display:block}.ticket-number{text-align:left;margin-top:16px}.grid{grid-template-columns:1fr}.toolbar{margin-left:16px;margin-right:16px}.ticket{width:calc(100% - 24px)} }
        @media print { @page { size:A4; margin:13mm; } body{background:#fff}.toolbar{display:none}.ticket{width:100%;margin:0;box-shadow:none;border-radius:0}.ticket-header{-webkit-print-color-adjust:exact;print-color-adjust:exact}.route,.notice{-webkit-print-color-adjust:exact;print-color-adjust:exact}.ticket-footer{padding-bottom:0} }
    </style>
</head>
<body>
@php
    $trip = $booking->trip;
    $request = $booking->tripRequest;
    $van = $trip?->van;
    $route = $trip?->route_name ?? $request?->destination ?? $booking->dropoff_location ?? 'Trip Details';
    $pickup = $trip?->pickup_location ?? $booking->pickup_location ?? 'Pickup location to be confirmed';
    $dropoff = $booking->dropoff_location ?? $trip?->dropoff_location ?? $request?->destination ?? 'Destination to be confirmed';
    $date = $booking->booking_date?->format('F d, Y') ?? $request?->date_of_travel?->format('F d, Y') ?? 'To be confirmed';
    $time = $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') : ($trip?->departure_time ? \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') : 'To be confirmed');
@endphp
<div class="toolbar">
    <a href="{{ route('user.dashboard') }}">← Back to dashboard</a>
    <button type="button" onclick="window.print()">Print Ticket</button>
</div>
<main class="ticket">
    <header class="ticket-header">
        <div>
            <div class="brand">VanService</div>
            <div class="subtitle">Confirmed Passenger Trip Ticket</div>
        </div>
        <div class="ticket-number">TICKET NUMBER<strong>REQ-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</strong></div>
    </header>
    <section class="ticket-body">
        <span class="status">CONFIRMED</span>
        <div class="route">
            <div class="location">{{ $pickup }}</div>
            <div class="arrow">↓</div>
            <div class="location">{{ $dropoff }}</div>
            <div style="margin-top:10px;color:#64748b;font-size:13px;font-weight:700;">{{ $route }}</div>
        </div>
        <div class="grid">
            <div class="item"><div class="label">Passenger</div><div class="value">{{ $booking->user->name }}</div></div>
            <div class="item"><div class="label">Drop-off Address</div><div class="value">{{ $dropoff }}</div></div>
            <div class="item"><div class="label">Date of Travel</div><div class="value">{{ $date }}</div></div>
            <div class="item"><div class="label">Departure Time</div><div class="value">{{ $time }}</div></div>
            <div class="item"><div class="label">Passengers / Seats</div><div class="value">{{ $booking->seats_requested }}</div></div>
            <div class="item"><div class="label">Assigned Van</div><div class="value">{{ $van?->van_model ?? $booking->van_model ?? 'To be confirmed' }}{{ $van?->van_plate ? ' — '.$van->van_plate : '' }}</div></div>
            <div class="item"><div class="label">Driver</div><div class="value">{{ $van?->driver?->name ?? 'To be confirmed' }}</div></div>
            <div class="item"><div class="label">Purpose</div><div class="value">{{ $booking->purpose ?? '—' }}</div></div>
            <div class="item"><div class="label">Booking Status</div><div class="value">{{ $booking->status }}</div></div>
        </div>
        <div class="notice">Please present this ticket to the driver or trip administrator before boarding. Keep it until the travel is completed.</div>
    </section>
    <footer class="ticket-footer">
        <span>Generated {{ now()->format('M d, Y h:i A') }}</span>
        <span>VanService Trip Management System</span>
    </footer>
</main>
</body>
</html>
