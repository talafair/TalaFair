@extends('layouts.app')

@section('title', 'Live Raffle')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card yg-card text-center mb-4">
      <div class="card-body p-4 p-md-5">
        <div class="small text-uppercase text-secondary fw-semibold">TalaFair live raffle</div>
        <h2 class="fw-bold mt-2">{{ $announcement->title }}</h2>
        <p class="text-secondary mb-0">All attendees have a chance to win. Points increase chances, but do not guarantee a win.</p>
      </div>
    </div>

    <div class="card yg-card">
      <div class="card-header bg-white fw-bold"><i class="bi bi-trophy me-1 text-yg"></i>Winners</div>
      <div id="raffle-winners" class="list-group list-group-flush">
        @forelse ($winners as $winner)
          <div class="list-group-item d-flex justify-content-between gap-3"><span>{{ $winner->prize->name }} <small class="text-secondary">({{ $winner->prize->prize_type_label }}{{ $winner->prize->isPointsPrize() ? ': +' . number_format($winner->prize->points_amount) . ' points' : '' }})</small></span><strong>{{ $winner->public_name }}</strong></div>
        @empty
          <div class="list-group-item text-secondary">The raffle has not started yet.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (() => {
    const list = document.getElementById('raffle-winners');
    const stateUrl = @json(route('announcements.raffle.state', $announcement));
    let last = '';
    const refresh = async () => {
      const response = await fetch(stateUrl, {headers: {'Accept': 'application/json'}});
      if (!response.ok) return;
      const data = await response.json();
      const current = JSON.stringify(data.winners);
      if (current === last) return;
      last = current;
      list.innerHTML = data.winners.length
        ? data.winners.map(winner => `<div class="list-group-item d-flex justify-content-between gap-3"><span>${escapeHtml(winner.prize)} <small class="text-secondary">(${escapeHtml(winner.prize_type)}${winner.points_awarded ? `: +${winner.points_awarded} points` : ''})</small></span><strong>${escapeHtml(winner.name)}</strong></div>`).join('')
        : '<div class="list-group-item text-secondary">The raffle has not started yet.</div>';
    };
    const escapeHtml = value => String(value).replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));
    refresh();
    const timer = window.setInterval(refresh, 5000);
    window.addEventListener('beforeunload', () => window.clearInterval(timer));
  })();
</script>
@endpush