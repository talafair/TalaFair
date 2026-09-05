@extends('layouts.app')

@section('title', 'Attendance Statistics')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-9">

    <a href="{{ route('announcements.show', $announcement) }}" class="small link-secondary">
      <i class="bi bi-arrow-left me-1"></i>{{ $announcement->title }}
    </a>
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-4">
      <h4 class="fw-bold mt-1 mb-0">Attendance statistics</h4>
      @if (auth()->user()->isOfficial())
        <div class="dropdown">
          <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Export reports
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><h6 class="dropdown-header">Announcement statistics</h6></li>
            <li><a class="dropdown-item" href="{{ route('announcements.summary.download', $announcement) }}"><i class="bi bi-download me-2"></i>Download summary PDF</a></li>
            <li><a class="dropdown-item" target="_blank" rel="noopener" href="{{ route('announcements.summary.print', $announcement) }}"><i class="bi bi-printer me-2"></i>Print summary</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header">Event attendance sheet</h6></li>
            <li><a class="dropdown-item" href="{{ route('announcements.attendance.download', $announcement) }}"><i class="bi bi-download me-2"></i>Download attendance PDF</a></li>
            <li><a class="dropdown-item" target="_blank" rel="noopener" href="{{ route('announcements.attendance.print', $announcement) }}"><i class="bi bi-printer me-2"></i>Print attendance sheet</a></li>
          </ul>
        </div>
      @endif
    </div>

    <div class="row g-3 mb-4">
      @foreach ([
        'Audience notified' => $stats['audience'],
        'Confirmed yes'     => $stats['attending'],
        'Said no'           => $stats['not_attending'],
        'No response'       => $stats['no_response'],
        'Checked in'        => $stats['scanned'],
        'Early check-ins'   => $stats['early'],
      ] as $label => $value)
        <div class="col-6 col-md-4">
          <div class="card yg-card h-100">
            <div class="card-body py-3">
              <div class="h4 fw-bold mb-0">{{ number_format($value) }}</div>
              <div class="small text-uppercase text-secondary">{{ $label }}</div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <p>Turnout against confirmations: <span class="fw-bold text-yg">{{ $stats['turnout_rate'] }}%</span></p>

    <div class="card yg-card overflow-hidden mb-4">
      <div class="card-header bg-white d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <span class="fw-bold">Check-in log</span>
        <form method="GET" class="d-flex gap-2">
          <label for="attendanceUserName" class="visually-hidden">Filter by user name</label>
          <input id="attendanceUserName" name="user_name" value="{{ $userName }}" class="form-control form-control-sm" placeholder="Search user name" autocomplete="off">
          <button class="btn btn-yg btn-sm" type="submit"><i class="bi bi-search me-1"></i>Search</button>
          @if ($userName !== '')
            <a href="{{ route('announcements.statistics', $announcement) }}" class="btn btn-outline-secondary btn-sm" title="Clear filter"><i class="bi bi-x-lg"></i></a>
          @endif
        </form>
      </div>
      <div class="list-group list-group-flush">
        @forelse ($attendees as $row)
          <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
            <div>
              <div class="fw-semibold">{{ $row->user->full_name }}</div>
              <div class="small font-monospace text-secondary">{{ $row->user->unique_id }}</div>
            </div>
            <div class="text-end">
              <div>{{ $row->scanned_at->format('g:i A') }}</div>
              <div class="small {{ $row->is_early ? 'text-warning-emphasis' : 'text-secondary' }}">
                {!! $row->is_early ? 'Early &middot; +10%' : 'On time' !!} &middot; +{{ $row->points_awarded }}
              </div>
            </div>
          </div>
        @empty
          <div class="list-group-item text-center text-secondary py-5">No one has checked in yet.</div>
        @endforelse
      </div>
    </div>

    @if ($announcement->raffle_enabled)
      <div class="card yg-card overflow-hidden mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <span class="fw-bold"><i class="bi bi-ticket-perforated me-1 text-yg"></i>Event raffle</span>
          @if (auth()->user()->isOfficial())
            <form method="POST" action="{{ route('announcements.raffle.draw', $announcement) }}">
              @csrf
              <button class="btn btn-yg btn-sm" {{ $raffleEntries->whereNull('selected_at')->isEmpty() ? 'disabled' : '' }}><i class="bi bi-shuffle me-1"></i>Draw winner</button>
            </form>
          @endif
        </div>
        <div class="card-body">
          <p class="small text-secondary">{{ $raffleEntries->count() }} attendee(s) entered automatically. Early check-ins have 1.10x weight; other attendees have 1.00x.</p>
          <div class="list-group list-group-flush">
            @forelse ($raffleEntries as $entry)
              <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span class="fw-semibold">{{ $entry->user->full_name }}</span>
                <span class="small {{ $entry->is_early ? 'text-warning-emphasis' : 'text-secondary' }}">{{ $entry->is_early ? 'Early · 1.10x' : 'On time · 1.00x' }}{{ $entry->selected_at ? ' · Selected' : '' }}</span>
              </div>
            @empty
              <div class="text-secondary small">No attendees have been entered yet.</div>
            @endforelse
          </div>
        </div>
      </div>
    @endif

    @if (auth()->user()->isOfficial() && $stats['reasons']->isNotEmpty())
      <div class="card yg-card overflow-hidden">
        <div class="card-header bg-white fw-bold">Why residents cannot attend</div>
        <ul class="list-group list-group-flush">
          @foreach ($stats['reasons'] as $reason)
            <li class="list-group-item small">{{ $reason->reason }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
  (() => {
    const form = document.getElementById('attendanceUserName')?.form;
    const input = document.getElementById('attendanceUserName');
    let timer;

    input?.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => form?.requestSubmit(), 350);
    });
  })();
</script>
@endpush

