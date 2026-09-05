<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $survey->title }} Summary</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; margin: 32px; }
        h1, h2, h3 { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; font-size: 12px; }
        th { background: #f3f4f6; }
        .meta { margin-bottom: 16px; line-height: 1.5; }
        .small { font-size: 12px; }
    </style>
</head>
<body>
    <h1>{{ $survey->title }}</h1>
    <div class="meta">
        <div><strong>Due:</strong> {{ $survey->due_at?->format('M j, Y g:i A') }}</div>
        <div><strong>Audience:</strong> {{ $survey->audience === 'event_attendees' ? 'Event attendees' : ucfirst(str_replace('_', ' ', $survey->audience)) }}</div>
        <div><strong>Answered:</strong> {{ $responses->count() }}</div>
        <div><strong>Overall average:</strong> {{ $overallAverage !== null ? number_format($overallAverage, 2) : '—' }} / 5</div>
    </div>

    <h2>Question-by-question averages</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Question</th>
                <th>Average</th>
                <th>Responses</th>
                <th>SD</th>
                <th>D</th>
                <th>N</th>
                <th>A</th>
                <th>SA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($questionBreakdown as $index => $questionSummary)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $questionSummary['question'] }}</td>
                    <td>{{ $questionSummary['average'] !== null ? number_format($questionSummary['average'], 2) : '—' }}</td>
                    <td>{{ $questionSummary['responses'] }}</td>
                    <td>{{ $questionSummary['counts'][1] }}</td>
                    <td>{{ $questionSummary['counts'][2] }}</td>
                    <td>{{ $questionSummary['counts'][3] }}</td>
                    <td>{{ $questionSummary['counts'][4] }}</td>
                    <td>{{ $questionSummary['counts'][5] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($survey->suggestion_enabled)
        <h2>Suggestions</h2>
        @forelse ($suggestions as $response)
            <div class="small">{{ $response->suggestion }}</div>
        @empty
            <div class="small">No suggestions submitted.</div>
        @endforelse
    @endif
</body>
</html>
