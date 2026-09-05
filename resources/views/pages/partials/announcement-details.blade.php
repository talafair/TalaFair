@php
    $categoryLabels = [
        'ice_breaker' => 'Ice Breaker',
        'q_and_a' => 'Q&A',
        'intermission' => 'Intermission',
    ];
@endphp

<p class="text-secondary mb-0" style="white-space: pre-line;">{{ $announcement->body }}</p>

@if ($announcement->is_event)
    <div class="bg-body-secondary rounded-3 p-3 mt-4 small">
        <div><strong>When:</strong> {{ $announcement->event_start_at?->format('M j, Y g:i A') }} to {{ $announcement->event_end_at?->format('g:i A') }}</div>
        <div><strong>Where:</strong> {{ $announcement->venue_name ?: 'Barangay San Jose' }}</div>
        <div><strong>For:</strong> {{ implode(', ', $announcement->audienceLabels()) }}</div>
        <div class="fw-semibold text-yg mt-2">
            @if ($announcement->isParticipationActivity())
                {{ $announcement->participation_points }} participation points per resident
            @else
                {{ $announcement->base_points }} base points, plus 10% for early scanning
            @endif
        </div>
    </div>
@endif
