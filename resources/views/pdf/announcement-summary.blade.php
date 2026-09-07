<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Announcement Summary - {{ $announcement->title }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { color: #24302b; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #176b52; font-size: 20px; margin: 0 0 4px; }
        h2 { border-bottom: 1px solid #cbd8d1; color: #176b52; font-size: 13px; margin: 22px 0 8px; padding-bottom: 4px; }
        .muted { color: #68756e; }
        .meta { margin: 0 0 16px; }
        .meta td { padding: 2px 18px 2px 0; }
        .stats { border-collapse: collapse; width: 100%; }
        .stats td { background: #eef5f1; border: 1px solid #d9e6df; padding: 9px; text-align: center; width: 16.66%; }
        .number { color: #176b52; font-size: 17px; font-weight: bold; }
        .label { color: #68756e; font-size: 8px; text-transform: uppercase; }
        table.data { border-collapse: collapse; width: 100%; }
        .data th { background: #176b52; color: white; font-size: 9px; padding: 7px; text-align: left; }
        .data td { border-bottom: 1px solid #d9e1dd; padding: 6px 7px; }
        .data tr { page-break-inside: avoid; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Announcement Statistics</h1>
    <div class="muted">Official report generated {{ now()->format('Y-m-d H:i') }}</div>
    <table class="meta">
        <tr><td><strong>Event</strong><br>{{ $announcement->title }}</td><td><strong>Date</strong><br>{{ $announcement->event_start_at?->format('F j, Y g:i A') ?? 'Not set' }}</td></tr>
        <tr><td><strong>Venue</strong><br>{{ $announcement->venue_name ?: 'Not set' }}</td><td><strong>Audience</strong><br>{{ implode(', ', $announcement->audienceLabels()) ?: 'Public' }}</td></tr>
    </table>

    <table class="stats">
        <tr>
            @foreach ([
                'Audience notified' => $stats['audience'],
                'Confirmed yes' => $stats['attending'],
                'Said no' => $stats['not_attending'],
                'No response' => $stats['no_response'],
                'Residents present' => $stats['resident_scanned'],
                'Officials present' => $stats['official_scanned'],
            ] as $label => $value)
                <td><div class="number">{{ number_format($value) }}</div><div class="label">{{ $label }}</div></td>
            @endforeach
        </tr>
    </table>
    <p><strong>Total checked in:</strong> {{ $stats['scanned'] }} | <strong>Early check-ins:</strong> {{ $stats['early'] }} | <strong>Turnout against confirmations:</strong> {{ $stats['turnout_rate'] }}%</p>

    <h2>Check-in log</h2>
    <table class="data">
        <thead><tr><th>Name</th><th>ID</th><th>Category</th><th>Checked in at</th><th>Type</th><th class="right">Points</th></tr></thead>
        <tbody>
        @forelse ($attendees as $attendance)
            <tr><td>{{ $attendance->user->full_name }}</td><td>{{ $attendance->user->unique_id }}</td><td>{{ ucfirst($attendance->user_category) }}</td><td>{{ $attendance->scanned_at->format('Y-m-d g:i A') }}</td><td>{{ $attendance->is_early ? 'Early' : 'On time' }}</td><td class="right">{{ $attendance->points_awarded }}</td></tr>
        @empty
            <tr><td colspan="6">No one has checked in yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
