@extends('layouts.app')

@section('title', 'Scan Attendance')

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-sm-10 col-md-7 col-lg-5">

    <h4 class="fw-bold mb-1"><i class="bi bi-qr-code-scan me-2 text-yg"></i>{{ $activityMode ? 'Scan activity participants' : ($officialMode ? 'Scan resident attendance' : 'Scan for attendance') }}</h4>
    <p class="text-secondary">
      Point your camera at the {{ $activityMode ? 'resident ID QR after each activity' : ($officialMode ? 'resident ID QR' : 'event QR') }}.
      Scanning opens two hours before the event starts and only works inside the venue.
    </p>

    @if ($announcement)
      <div class="alert alert-light border">
        <strong>{{ $announcement->title }}</strong>
        <div class="small text-secondary">{{ $announcement->event_start_at?->format('M j, Y g:i A') }}</div>
      </div>
    @elseif ($officialMode)
      <label for="event-select" class="form-label small fw-semibold mb-1">Event to record attendance for</label>
      <select id="event-select" class="form-select form-select-sm mb-3" @disabled($openEvents->isEmpty())>
        <option value="">Select an event</option>
        @foreach ($openEvents as $event)
          <option value="{{ $event->id }}">{{ $event->title }} · {{ $event->event_start_at->format('M j, g:i A') }}</option>
        @endforeach
      </select>
    @endif

    @if ($openEvents->isEmpty())
      <div class="card yg-card">
        <div class="card-body text-center text-secondary py-5">
          <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
          No event is open for scanning right now.
          <a href="{{ url('/') }}" class="d-block mt-2 fw-semibold">Back to home</a>
        </div>
      </div>
    @else
      <ul class="list-group mb-3">
        @foreach ($openEvents as $event)
          <li class="list-group-item">
            <div class="fw-semibold">{{ $event->title }}</div>
            <div class="small text-secondary">
              {{ $event->event_start_at->format('M j, g:i A') }} &middot;
              {{ $event->venue_name ?: 'Barangay San Jose' }}
            </div>
          </li>
        @endforeach
      </ul>
    @endif

    <div id="reader" class="rounded-3 overflow-hidden bg-dark mx-auto" style="width: min(100%, 280px); aspect-ratio: 1 / 1;"></div>

    <div id="result" class="alert d-none mt-3 rounded-3" role="alert"></div>

    <div id="permission-help" class="alert alert-light border mt-3">
      <div class="fw-semibold"><i class="bi bi-shield-check me-1 text-yg"></i>Camera and location permission required</div>
      <div class="small text-secondary mt-1">Allow camera access to read the QR code and location access so TalaFair can confirm you are at the venue. Nothing starts until you choose Open camera.</div>
    </div>

    @if ($officialMode && ! $activityMode)
      <button id="manual-toggle" type="button" class="btn btn-outline-dark w-100 mt-3">
        <i class="bi bi-keyboard me-1"></i>QR code not scanning? Enter Resident Unique ID instead
      </button>
      <div class="form-text mt-2">You can keep trying the QR scanner as many times as needed, or use the manual Unique ID option.</div>
      <form id="manual-form" class="border rounded-3 p-3 mt-3 d-none">
        <label for="unique-id" class="form-label fw-semibold">Resident Unique ID Number</label>
        <div class="input-group">
          <input id="unique-id" class="form-control" placeholder="Z2-26-000000001" maxlength="32" autocomplete="off">
          <button class="btn btn-dark" type="submit">Record attendance</button>
        </div>
        <div class="form-text">Enter the resident's existing Unique ID Number from their ID card.</div>
        <button id="manual-back" type="button" class="btn btn-link btn-sm px-0">Back to scanner</button>
      </form>
    @endif

    @if (! $officialMode)
      <div id="resident-id-fallback" class="alert alert-warning d-none mt-3">
        <div class="fw-semibold"><i class="bi bi-person-badge me-1"></i>QR scanning is unavailable.</div>
        <div class="small mt-1">Ask an official to scan your digital ID card instead.</div>
        <a href="{{ route('id-card.show') }}" class="btn btn-sm btn-warning mt-2"><i class="bi bi-person-badge me-1"></i>Open my ID card</a>
      </div>
    @endif

    <div class="d-flex gap-2 mt-3">
      <button id="start-btn" class="btn btn-primary flex-fill fw-semibold">
        <i class="bi bi-camera me-1"></i>Open camera
      </button>
      <button id="stop-btn" class="btn btn-outline-secondary flex-fill fw-semibold d-none">Stop</button>
    </div>

    <p id="gps-status" class="form-text mt-2">
      <i class="bi bi-geo-alt me-1"></i>Location is not shared yet.
    </p>

    <a href="{{ route('attendance.history') }}" class="btn btn-link btn-sm px-0">
      <i class="bi bi-clock-history me-1"></i>My attendance history
    </a>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
  const CHECK_URL = @json(route('attendance.check'));
  const CSRF      = @json(csrf_token());
  const PREFILLED = @json($prefilledToken);
  const ANNOUNCEMENT_ID = @json($announcement?->id);
  const OFFICIAL = @json($officialMode);
  const ACTIVITY = @json($activityMode);

  const resultBox = document.getElementById('result');
  const gpsStatus = document.getElementById('gps-status');
  const startBtn  = document.getElementById('start-btn');
  const stopBtn   = document.getElementById('stop-btn');
  const manualForm = document.getElementById('manual-form');
  const manualToggle = document.getElementById('manual-toggle');
  const manualBack = document.getElementById('manual-back');
  const eventSelect = document.getElementById('event-select');

  let scanner = null, busy = false, completed = false, scannerPaused = false, scannerRunning = false;
  let selectedAnnouncementId = ANNOUNCEMENT_ID;

  eventSelect?.addEventListener('change', () => {
    selectedAnnouncementId = eventSelect.value || null;
  });

  function show(ok, html) {
    resultBox.className = 'alert mt-3 rounded-3 ' + (ok ? 'alert-success' : 'alert-danger');
    resultBox.innerHTML = html;
  }

  function status(message, tone = 'info') {
    resultBox.className = 'alert mt-3 rounded-3 alert-' + tone;
    resultBox.textContent = message;
  }

  function position() {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) return reject(new Error('This device cannot share its location.'));
      navigator.geolocation.getCurrentPosition(
        p => resolve(p.coords),
        () => reject(new Error('Turn on location so the barangay can confirm you are at the venue.')),
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    });
  }

  async function submit(token) {
    token = typeof token === 'string' ? token.trim() : '';
    if (!token || busy || completed) return;
    busy = true;
    status('Processing QR code…', 'info');
    try {
      gpsStatus.innerHTML = '<i class="bi bi-geo-alt me-1"></i>Checking your location…';
      const coords = await position();
      gpsStatus.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i>Location accurate to about ' +
                            Math.round(coords.accuracy) + ' m.';

      const res = await fetch(ACTIVITY ? @json($announcement ? route('announcements.participation', $announcement) : route('attendance.check')) : CHECK_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({
          token: token,
          announcement_id: selectedAnnouncementId,
          latitude: coords.latitude,
          longitude: coords.longitude,
          accuracy: coords.accuracy
        })
      });

      const data = await res.json().catch(() => ({
        ok: false,
        message: 'The attendance service is temporarily unavailable. Please try again.'
      }));

      if (data.ok) {
        completed = !OFFICIAL;
        show(true, ACTIVITY
          ? '<div class="fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>' + data.message + '</div>'
          :
          '<div class="fw-semibold mb-2"><i class="bi bi-check-circle-fill me-1"></i>' + data.message + '</div>' +
          '<ul class="small mb-0 ps-3">' +
            '<li>Event: ' + data.event + '</li>' +
            '<li>Base points: ' + data.breakdown.base + '</li>' +
            '<li>Engagement bonus: +' + data.breakdown.early_bonus + '</li>' +
            '<li class="fw-semibold">Total: ' + data.breakdown.total + '</li>' +
          '</ul>');
        if (scanner) scanner.stop().catch(() => {});
        scannerPaused = false;
        stopBtn.classList.add('d-none');
        startBtn.classList.remove('d-none');
      } else {
        const scanMessage = data.message || 'Attendance was not recorded. That scan could not be accepted.';
        if (OFFICIAL) {
          manualForm?.classList.remove('d-none');
          manualToggle?.classList.add('d-none');
          show(false, '<div class="fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>' + scanMessage + '</div><div class="small mt-1">You can keep trying the scanner or enter the resident\'s Unique ID Number below.</div>');
        } else {
          show(false, scanMessage);
          document.getElementById('resident-id-fallback')?.classList.remove('d-none');
        }
      }
    } catch (err) {
      show(false, 'Unable to record attendance. Please try again.');
    } finally {
      if (scannerPaused && scanner) {
        try { scanner.resume(); } catch (e) {}
        scannerPaused = false;
      }
      busy = false;
    }
  }

  manualForm?.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (completed || busy) return;
    busy = true;
    try {
      const uniqueId = document.getElementById('unique-id');
      if (!uniqueId.value.trim()) {
        show(false, 'Enter the resident unique ID from their ID card.');
        return;
      }
      const coords = await position();
      const res = await fetch(@json(route('attendance.check-by-id')), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({
          announcement_id: selectedAnnouncementId,
          unique_id: uniqueId.value,
          latitude: coords.latitude,
          longitude: coords.longitude
        })
      });
      const data = await res.json().catch(() => ({
        ok: false,
        message: 'The attendance service is temporarily unavailable. Please try again.'
      }));
      if (data.ok) {
        completed = !OFFICIAL;
        uniqueId.value = '';
        show(true, '<div class="fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Attendance recorded successfully.</div><div class="small mt-1">' + data.resident + ' · ' + data.message + '</div>');
      } else {
        show(false, data.message || 'Unique QR ID not found. Please check the ID and try again.');
      }
    } catch (err) {
      show(false, 'Unable to record attendance. Please try again.');
    } finally {
      busy = false;
    }
  });

  manualToggle?.addEventListener('click', () => {
    manualForm?.classList.remove('d-none');
    manualToggle.classList.add('d-none');
    if (scanner) scanner.stop().catch(() => {});
    stopBtn.classList.add('d-none');
    startBtn.classList.remove('d-none');
  });

  manualBack?.addEventListener('click', () => {
    manualForm.classList.add('d-none');
    manualToggle?.classList.remove('d-none');
  });

  startBtn.addEventListener('click', async function () {
    if (completed) return;
    if (OFFICIAL && !selectedAnnouncementId) {
      show(false, 'Select an event before starting the official attendance scanner.');
      eventSelect?.focus();
      return;
    }
    scanner = scanner || new Html5Qrcode('reader');
    try {
      status('Starting camera…', 'info');
      gpsStatus.innerHTML = '<i class="bi bi-geo-alt me-1"></i>Requesting location permission…';
      await position();
      gpsStatus.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i>Location permission granted. Starting camera…';
      await scanner.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 220, height: 220 } },
        text => {
          if (busy || completed) return;
          try {
            scanner.pause(true);
            scannerPaused = true;
          } catch (e) {}
          submit(text);
        },
        () => {}
      );
      scannerRunning = true;
      status('Scanning for a QR code…', 'info');
      startBtn.classList.add('d-none');
      stopBtn.classList.remove('d-none');
      document.getElementById('permission-help')?.classList.add('d-none');
    } catch (e) {
      const cameraMessage = e.message.includes('location')
        ? e.message + ' Allow location access, then press Open camera again.'
        : 'Camera access was denied or could not start. Allow camera access in your browser settings, then try again. On a phone this page must be served over HTTPS.';
      show(false, OFFICIAL && manualToggle
        ? cameraMessage + '<div class="small fw-semibold mt-2">Use the Resident Unique ID Number fallback below if scanning is unavailable.</div>'
        : cameraMessage);
      if (OFFICIAL && manualForm && manualToggle) {
        manualForm.classList.remove('d-none');
        manualToggle.classList.add('d-none');
      }
      gpsStatus.innerHTML = '<i class="bi bi-geo-alt me-1"></i>Camera and location permission are still required.';
    }
  });

  stopBtn.addEventListener('click', function () {
    if (scanner && scannerRunning) scanner.stop().catch(() => {});
    scannerRunning = false;
    scannerPaused = false;
    status('Camera stopped. Select Open camera to try again.', 'secondary');
    stopBtn.classList.add('d-none');
    startBtn.classList.remove('d-none');
  });

  window.addEventListener('pagehide', () => {
    if (scanner && scannerRunning) scanner.stop().catch(() => {});
  });

  if (PREFILLED) {
    status('Processing event QR code…', 'info');
    submit(PREFILLED);
  }
})();
</script>
@endpush

