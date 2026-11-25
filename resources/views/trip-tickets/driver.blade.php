<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Driver's Trip Ticket — Trip {{ $trip->id }}</title>
    <style>
        *{box-sizing:border-box} body{margin:0;background:#e9edf2;color:#111;font-family:Arial,Helvetica,sans-serif}.toolbar{width:210mm;margin:14px auto;display:flex;justify-content:space-between;gap:8px}.toolbar a,.toolbar button{border:1px solid #b8c1ca;border-radius:5px;padding:9px 14px;background:#fff;color:#1f2937;text-decoration:none;font-weight:700;cursor:pointer}.toolbar button{background:#173d69;color:#fff;border-color:#173d69}.paper{width:210mm;min-height:297mm;margin:0 auto 24px;background:#fff;padding:10mm 12mm 9mm;box-shadow:0 2px 12px #0002}.masthead{display:grid;grid-template-columns:27mm 1fr;align-items:center;gap:8mm;border-bottom:2px solid #333;padding-bottom:6px}.brand-logo{width:25mm;height:25mm;object-fit:contain;display:block}.cert-logos{display:flex;align-items:flex-end;justify-content:flex-end;gap:3mm}.cert-logo{width:22mm;height:14mm;object-fit:contain}.iso-logo{width:27mm;height:19mm;object-fit:contain}.agency{text-align:left;color:#222;line-height:1.12}.agency .republic{font-size:11px}.agency .university{font-size:17px;font-weight:800;letter-spacing:.35px}.agency .campus{font-size:15px;font-weight:700}.agency .motorpool{font-size:16px;font-weight:800}.heading{position:relative;text-align:center;font-size:17px;font-weight:800;margin:9px 0 2px}.appendix{position:absolute;right:0;top:0;font-size:10px;font-weight:700}.meta{text-align:right;font-size:12px;margin-top:2px}.meta-line{display:inline-block;min-width:28mm;border-bottom:1px solid #222;text-align:center;margin-left:5px}.section-title{font-weight:700;margin:14px 0 7px;font-size:13px}.form-row{display:grid;grid-template-columns:7mm 58mm 1fr;align-items:end;min-height:8mm;font-size:12px}.form-row .line{border-bottom:1px solid #555;min-height:18px;padding:1px 4px;font-weight:700}.route-row{display:grid;grid-template-columns:7mm 1fr;min-height:10mm;font-size:12px}.route-box{border-bottom:1px solid #555;line-height:1.5}.signature-block{text-align:right;margin-top:10mm;font-size:11px}.signature{font-weight:800;text-decoration:underline}.driver-section{margin-top:8px}.driver-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 12mm}.driver-field{display:grid;grid-template-columns:7mm 1fr;min-height:8mm;font-size:12px;align-items:end}.driver-field.wide{grid-column:1/-1}.driver-field .line{border-bottom:1px solid #555;min-height:18px;padding:1px 3px}.fuel-title{text-align:center;font-weight:700;letter-spacing:2px;margin:8px 0 2px}.fuel-line{display:grid;grid-template-columns:10mm 1fr 24mm;min-height:7mm;font-size:12px;align-items:end}.fuel-subline{margin-left:8mm;grid-template-columns:10mm 1fr 24mm}.fuel-line .line{border-bottom:1px solid #555;min-height:17px}.passenger-title{margin-top:10px;font-weight:700;font-size:12px}.passenger-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:5px 14px;margin-top:5px}.passenger{font-size:10px;border-bottom:1px solid #555;min-height:19px;padding:2px 0}.certify{font-size:11px;margin-top:9mm;text-align:center}.certify .signature-block{margin-top:7mm;margin-right:12mm}.footer{border-top:1px solid #888;margin-top:7mm;padding-top:3px;display:flex;align-items:center;justify-content:space-between;gap:8mm;color:#666;font-size:9px}.footer-copy{text-align:left;flex:1;line-height:1.35}.footer .cert-logos{display:flex;flex-direction:row;align-items:center;justify-content:flex-end;gap:3mm;flex:none}.small-muted{font-size:10px;color:#555}.time-value{font-weight:700;white-space:nowrap;border-bottom:1px solid #555;display:inline-block;min-width:32mm;padding:1px 4px}@media print{body{background:#fff}.toolbar{display:none}.paper{margin:0;box-shadow:none;width:210mm;min-height:297mm;padding:9mm 11mm 7mm}@page{size:A4;margin:0}}@media(max-width:900px){.toolbar{width:calc(100% - 24px)}.paper{width:calc(100% - 24px);padding:22px;overflow:auto}.masthead{min-width:650px}.paper{min-height:0}.driver-grid{min-width:650px}.passenger-grid{min-width:650px}}
    </style>
</head>
<body>
@php
    $van = $trip->van;
    $driver = $van?->driver;
    $passengers = $bookings;
    $confirmedPassengers = $passengers->sum('seats_requested');
    $latestFuel = $trip->fuelRecords->sortByDesc('fuel_date')->first();
    $date = optional($trip->trip_date)->format('F d, Y');
    $departure = $trip->departure_time ? \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') : '';
    $departureFromGarage = $trip->departed_at?->format('h:i A') ?: $departure;
    $arrivalAtDestination = $trip->arrived_at?->format('h:i A') ?: '';
@endphp
<div class="toolbar"><a href="javascript:history.back()">← Back</a><button onclick="window.print()">Print Driver's Trip Ticket</button></div>
<main class="paper">
    <header class="masthead">
        <img class="brand-logo" src="{{ asset('images/nisu-logo.png') }}" alt="Northern Iloilo State University logo">
        <div class="agency"><div class="republic">Republic of the Philippines</div><div class="university">NORTHERN ILOILO STATE UNIVERSITY</div><div class="campus">Lemery Campus</div><div class="motorpool">MOTORPOOL</div></div>
    </header>
    <div class="heading">DRIVER’S TRIP TICKET <span class="appendix">Appendix A</span></div>
    <div class="meta">No. <span class="meta-line">TRIP-{{ str_pad($trip->id, 5, '0', STR_PAD_LEFT) }}</span><br><span class="meta-line">{{ $date }}</span><br>Date</div>

    <div class="section-title">A. &nbsp; To be filled by the Administrative Official Authorizing Official Travel:</div>
    <div class="form-row"><span>1.</span><span>Name of Driver of the Vehicle</span><span class="line">{{ $driver?->name ?? 'To be assigned' }}</span></div>
    <div class="form-row"><span>2.</span><span>Government car to be used. Plate No.</span><span class="line">{{ $van?->van_plate ?? '—' }} — {{ $van?->van_model ?? $trip->van_model }}</span></div>
    <div class="form-row"><span>3.</span><span>Name/s of authorized passengers</span><span class="line">{{ $confirmedPassengers }} passenger(s) — see passenger list below</span></div>
    <div class="form-row"><span>4.</span><span>Place or places to be visited/inspected</span><span class="line">{{ $trip->pickup_location ?: 'Lemery Campus' }} → {{ $trip->dropoff_location ?: $trip->route_name }}</span></div>
    <div class="form-row"><span>5.</span><span>Purpose</span><span class="line">Official university travel / {{ $trip->route_name }}</span></div>
    <div class="signature-block"><span class="signature">RODEL P. SEPANTON, LLB</span><br>Administrative Officer V</div>

    <div class="section-title driver-section">B. &nbsp; To be filled by the driver</div>
    <div class="driver-grid">
        <div class="driver-field wide"><span>1.</span><span>Time of departure from Office/Garage <span class="time-value">{{ $departureFromGarage }}</span></span></div>
        <div class="driver-field wide"><span>2.</span><span>Time of arrival at destination <span class="time-value">{{ $arrivalAtDestination }}</span></span></div>
        <div class="driver-field wide"><span>3.</span><span>Time of departure from destination <span class="time-value"></span></span></div>
        <div class="driver-field wide"><span>4.</span><span>Time of arrival back to Office/Garage <span class="time-value"></span></span></div>
        <div class="driver-field wide"><span>5.</span><span>Approximate distance travelled (to and from) <span class="line">{{ $trip->distance_km > 0 ? number_format($trip->distance_km, 2) : '' }}</span> km</span></div>
    </div>
    <div class="fuel-title">GASOLINE ISSUED, PURCHASED, AND CONSUMED</div>
    <div class="fuel-line fuel-subline"><span>a.</span><span>Balance in tank</span><span class="line"> </span></div>
    <div class="fuel-line fuel-subline"><span>b.</span><span>Issued by Office from stock</span><span class="line"> </span></div>
    <div class="fuel-line fuel-subline"><span>c.</span><span>Add-purchased during trip</span><span class="line">{{ $latestFuel?->liters }}</span></div>
    <div class="fuel-line fuel-subline"><span></span><span style="text-align:center;font-weight:700;letter-spacing:2px">TOTAL</span><span class="line">{{ $latestFuel?->liters }}</span></div>
    <div class="fuel-line fuel-subline"><span>d.</span><span>Deduct Used during the trip</span><span class="line"> </span></div>
    <div class="fuel-line fuel-subline"><span>e.</span><span>Balance in tank at the end of the trip</span><span class="line"> </span></div>
    <div class="fuel-line"><span>7.</span><span>Gear oil used</span><span class="line"> </span></div>
    <div class="fuel-line"><span>8.</span><span>Lubricant oil issued</span><span class="line"> </span></div>
    <div class="fuel-line"><span>9.</span><span>Grease issued</span><span class="line"> </span></div>
    <div class="fuel-line"><span>10.</span><span>Speedometer reading — beginning / end of trip</span><span class="line"> </span></div>
    <div class="fuel-line"><span>11.</span><span>Remarks: {{ $trip->status === 'completed' ? 'Trip completed' : '' }}</span><span class="line"></span></div>

    <div class="certify">I hereby certify to the correctness of the above statement record of travel.
        <div class="signature-block"><span class="signature">{{ strtoupper($driver?->name ?? 'DRIVER') }}</span><br>Driver</div>
    </div>
    <div class="passenger-title">Booking Requester / Passenger, Drop-off Location &amp; Signature</div>
    <div class="passenger-grid">
        @forelse($passengers as $booking)
            <div class="passenger">
                <strong>{{ $booking->user?->name ?? 'Passenger' }}</strong>
                — Drop-off: {{ $booking->dropoff_location ?: ($trip->dropoff_location ?: 'Not specified') }}
                ({{ max(1, (int) $booking->seats_requested) }} seat(s)) &amp; Sig.
            </div>
        @empty
            @for($i = 0; $i < 9; $i++) <div class="passenger">Booking requester / drop-off location &amp; signature</div> @endfor
        @endforelse
    </div>
    <div class="footer"><div class="footer-copy">NISU Lemery Campus, Lemery, Iloilo &nbsp;|&nbsp; VanService Trip Management System<br>Generated {{ now()->format('M d, Y h:i A') }}</div><div class="cert-logos"><img class="iso-logo" src="{{ asset('images/iso-9001-ukas.jpg') }}" alt="ISO 9001 UKAS"><img class="cert-logo" src="{{ asset('images/nisu-ribbon-logo.png') }}" alt="NISU ribbon logo"></div></div>
</main>
</body>
</html>
