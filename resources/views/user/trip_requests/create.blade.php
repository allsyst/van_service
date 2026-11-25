<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Request Trip - VanService</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f2f8; font-family: 'Nunito', sans-serif; margin: 0; padding: 0; }

        /* Top Bar */
        .top-bar {
            background: linear-gradient(90deg, #1a1f36, #2d3561);
            color: #fff;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }
        .top-bar .brand { font-size: 1.15rem; font-weight: 800; letter-spacing: 0.5px; }
        .top-bar .brand i { color: #4e73df; }

        /* Page Hero */
        .page-hero {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #fff;
            padding: 2rem 2rem 1.5rem;
        }
        .page-hero h2 { font-size: 1.6rem; font-weight: 800; margin-bottom: 0.25rem; }
        .page-hero p { opacity: 0.85; margin-bottom: 0; font-size: 0.9rem; }

        /* Section Card */
        .section-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            margin-bottom: 1.5rem;
            overflow: visible;
        }
        .section-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-card-header .icon-wrap {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .section-card-header h5 { margin: 0; font-weight: 700; font-size: 1rem; }
        .section-card-header p { margin: 0; font-size: 0.78rem; color: #888; }
        .section-card-body { padding: 1.5rem; }

        /* Field Labels */
        .field-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #4e73df;
            margin-bottom: 0.4rem;
            display: block;
        }

        /* Input with icon */
        .input-icon-wrap { position: relative; }
        .input-icon-wrap .input-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #aaa; font-size: 0.85rem; z-index: 2;
        }
        .input-icon-wrap input,
        .input-icon-wrap select,
        .input-icon-wrap textarea {
            padding-left: 2.2rem;
            border-radius: 0.5rem;
            border: 1.5px solid #e0e4ef;
            font-size: 0.9rem;
        }
        .input-icon-wrap input { height: 42px; }
        .input-icon-wrap textarea { padding-top: 0.6rem; height: auto; }
        .input-icon-wrap input:focus,
        .input-icon-wrap select:focus,
        .input-icon-wrap textarea:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78,115,223,.12);
            outline: none;
        }

        /* Passenger Name Rows */
        .passenger-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .passenger-row input {
            flex: 1;
            border-radius: 0.5rem;
            border: 1.5px solid #e0e4ef;
            padding: 0.45rem 0.75rem;
            font-size: 0.88rem;
            height: 38px;
        }
        .passenger-row input:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78,115,223,.12);
            outline: none;
        }
        .btn-remove-passenger {
            background: none;
            border: none;
            color: #e74a3b;
            font-size: 1rem;
            cursor: pointer;
            padding: 0 4px;
            flex-shrink: 0;
        }
        .btn-remove-passenger:hover { color: #c0392b; }
        .btn-add-passenger {
            background: #eef2ff;
            border: 1.5px dashed #4e73df;
            color: #4e73df;
            border-radius: 0.5rem;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 0.25rem;
            transition: background .15s;
        }
        .btn-add-passenger:hover { background: #dce6ff; }

        /* Passenger count badge */
        .passenger-count-badge {
            display: inline-flex;
            align-items: center;
            background: #eef2ff;
            color: #4e73df;
            border-radius: 2rem;
            padding: 0.2rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 700;
            margin-left: 0.5rem;
        }

        /* Submit Buttons */
        .btn-save {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff; border: none;
            border-radius: 0.6rem;
            padding: 0.65rem 2rem;
            font-weight: 700; font-size: 0.95rem;
            transition: opacity .2s;
        }
        .btn-save:hover { opacity: 0.9; color: #fff; }
        .btn-cancel {
            border-radius: 0.6rem;
            padding: 0.65rem 1.5rem;
            font-weight: 600;
        }

        /* Validation alert */
        .alert-validation {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.88rem;
        }

        /* Request No preview */
        .req-no-preview {
            display: inline-flex;
            align-items: center;
            background: #f0f4ff;
            border: 1.5px solid #c5d3f8;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.88rem;
            color: #4e73df;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="brand"><i class="fas fa-shuttle-van mr-2"></i>VanService</div>
        <div class="d-flex align-items-center">
            <span class="text-white mr-3" style="font-size:0.88rem;opacity:.85;">
                <i class="fas fa-user-circle mr-1"></i>{{ auth()->user()->name }}
            </span>
            <a href="{{ route('user.trip_requests.index') }}" class="btn btn-sm btn-outline-light mr-2">
                <i class="fas fa-list mr-1"></i>My Requests
            </a>
            <a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-outline-light mr-2">
                <i class="fas fa-arrow-left mr-1"></i>Back
            </a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Page Hero -->
    <div class="page-hero">
        <h2><i class="fas fa-paper-plane mr-2"></i>Request Trip</h2>
        <p>Submit a trip request for admin approval. You will be notified once it is reviewed.</p>
    </div>

    <div class="container-fluid px-3 px-md-4 py-4" style="max-width:800px;">

        @if($errors->any())
        <div class="alert-validation">
            <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('user.trip_requests.store') }}" id="requestForm">
            @csrf

            <!-- Section 1: Request Details -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="icon-wrap" style="background:#eef2ff;">
                        <i class="fas fa-file-alt" style="color:#4e73df;"></i>
                    </div>
                    <div>
                        <h5>Trip Request Details</h5>
                        <p>Basic information about your trip request</p>
                    </div>
                </div>
                <div class="section-card-body">
                    <div class="row">

                        <!-- Request No (auto-generated preview) -->
                        <div class="col-md-6 mb-3">
                            <label class="field-label">Request No.</label>
                            <div class="req-no-preview">
                                <i class="fas fa-hashtag mr-2"></i>
                                Auto-generated on submit
                            </div>
                        </div>

                        <!-- Date of Travel -->
                        <div class="col-md-6 mb-3">
                            <label class="field-label">Date of Travel</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-calendar-alt input-icon"></i>
                                <input type="date" name="date_of_travel"
                                    class="form-control @error('date_of_travel') is-invalid @enderror"
                                    value="{{ old('date_of_travel', '') }}"
                                    min="{{ date('Y-m-d') }}" required>
                                @error('date_of_travel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Purpose -->
                        <div class="col-md-6 mb-3">
                            <label class="field-label">Purpose</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-bullseye input-icon"></i>
                                <input type="text" name="purpose"
                                    class="form-control @error('purpose') is-invalid @enderror"
                                    placeholder="e.g. Educational Tour, Field Trip..."
                                    value="{{ old('purpose', '') }}" required>
                                @error('purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Destination -->
                        <div class="col-md-6 mb-3">
                            <label class="field-label">Destination</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-map-marker-alt input-icon" style="color:#e74a3b;"></i>
                                <input type="text" id="tripRequestDestination" name="destination"
                                    class="form-control @error('destination') is-invalid @enderror"
                                    placeholder="e.g. Iloilo City, Manila..."
                                    value="{{ old('destination', '') }}" required>
                                <input type="hidden" id="destination_latitude" name="destination_latitude" value="{{ old('destination_latitude') }}">
                                <input type="hidden" id="destination_longitude" name="destination_longitude" value="{{ old('destination_longitude') }}">
                                @error('destination')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Section 2: Passengers -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="icon-wrap" style="background:#e8f8f5;">
                        <i class="fas fa-users" style="color:#1cc88a;"></i>
                    </div>
                    <div>
                        <h5>
                            Passengers
                            <span class="passenger-count-badge" id="passengerCountBadge">1 passenger</span>
                        </h5>
                        <p>List all passengers who will be joining this trip</p>
                    </div>
                </div>
                <div class="section-card-body">

                    <!-- Hidden field for no_of_passengers (auto-updated) -->
                    <input type="hidden" name="no_of_passengers" id="no_of_passengers" value="1">

                    <div id="passengerList">
                        <!-- Passenger rows injected here by JS -->
                    </div>

                    <button type="button" class="btn-add-passenger" id="addPassengerBtn">
                        <i class="fas fa-plus mr-1"></i> Add Passenger
                    </button>

                    @error('passenger_names')
                        <div class="text-danger mt-2" style="font-size:0.85rem;">{{ $message }}</div>
                    @enderror
                    @error('passenger_names.*')
                        <div class="text-danger mt-2" style="font-size:0.85rem;">{{ $message }}</div>
                    @enderror

                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex align-items-center gap-3 mb-5">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Request
                </button>
                <a href="{{ route('user.dashboard') }}" class="btn btn-secondary btn-cancel">
                    <i class="fas fa-times mr-1"></i>Cancel
                </a>
            </div>

        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    var passengerCount = 0;

    // Save a precise destination for the foreground arrival geofence. If a
    // lookup fails, the request still submits normally without auto-arrival.
    var tripRequestForm = document.querySelector('form');
    tripRequestForm.addEventListener('submit', function(event) {
        var latitudeField = document.getElementById('destination_latitude');
        var longitudeField = document.getElementById('destination_longitude');
        var destination = document.getElementById('tripRequestDestination').value.trim();

        if (latitudeField.value || longitudeField.value || !destination) return;

        event.preventDefault();
        var submitButton = tripRequestForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Saving destination...';

        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(destination) + '&limit=1&countrycodes=ph')
            .then(function(response) { return response.json(); })
            .then(function(results) {
                if (results && results[0]) {
                    latitudeField.value = parseFloat(results[0].lat).toFixed(7);
                    longitudeField.value = parseFloat(results[0].lon).toFixed(7);
                }
                tripRequestForm.submit();
            })
            .catch(function() {
                tripRequestForm.submit();
            });
    });

    function updateCount() {
        var rows = document.querySelectorAll('#passengerList .passenger-row');
        passengerCount = rows.length;
        document.getElementById('no_of_passengers').value = passengerCount;
        var badge = document.getElementById('passengerCountBadge');
        badge.textContent = passengerCount + (passengerCount === 1 ? ' passenger' : ' passengers');
    }

    function addPassengerRow(value) {
        var list = document.getElementById('passengerList');
        var idx  = list.children.length;
        var row  = document.createElement('div');
        row.className = 'passenger-row';
        row.innerHTML =
            '<span style="color:#888;font-size:0.8rem;font-weight:700;min-width:22px;text-align:right;">' + (idx + 1) + '.</span>' +
            '<input type="text" name="passenger_names[]" placeholder="Full name of passenger ' + (idx + 1) + '" value="' + (value || '') + '" required>' +
            '<button type="button" class="btn-remove-passenger" title="Remove"><i class="fas fa-times-circle"></i></button>';
        row.querySelector('.btn-remove-passenger').addEventListener('click', function() {
            row.remove();
            renumberRows();
            updateCount();
        });
        list.appendChild(row);
        updateCount();
    }

    function renumberRows() {
        var rows = document.querySelectorAll('#passengerList .passenger-row');
        rows.forEach(function(row, i) {
            row.querySelector('span').textContent = (i + 1) + '.';
            row.querySelector('input').placeholder = 'Full name of passenger ' + (i + 1);
        });
    }

    document.getElementById('addPassengerBtn').addEventListener('click', function() {
        addPassengerRow('');
        // Focus the new input
        var inputs = document.querySelectorAll('#passengerList .passenger-row input');
        if (inputs.length) inputs[inputs.length - 1].focus();
    });

    // Pre-fill old values on validation error
    @if(old('passenger_names'))
        @foreach(old('passenger_names') as $name)
            addPassengerRow('{{ addslashes($name) }}');
        @endforeach
    @else
        // Start with 1 empty row
        addPassengerRow('');
    @endif
    </script>
</body>
</html>
