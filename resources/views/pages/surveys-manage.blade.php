@extends('layouts.app')

@section('title', 'Manage Surveys')

@section('content')
<div class="page-header p-4 p-lg-5 mb-4"><h2 class="fw-bold mb-1"><i class="bi bi-bar-chart-line me-2"></i>Community surveys</h2><p class="mb-0 opacity-75">Publish questions for residents to answer using a Likert scale.</p></div>

<div class="card yg-card mb-4"><div class="card-body p-4">
  <h5 class="fw-bold mb-3">Publish a survey</h5>
  <form method="POST" action="{{ route('surveys.store') }}" id="surveyForm">@csrf
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label fw-semibold">Title</label><input name="title" class="form-control" value="{{ old('title') }}" placeholder="Community event feedback" required></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Description <span class="text-secondary fw-normal">(optional)</span></label><input name="description" class="form-control" value="{{ old('description') }}" placeholder="Tell residents what this survey is about"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Due date and time</label><input type="datetime-local" name="due_at" class="form-control" value="{{ old('due_at') }}" required></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Points for answering</label><input type="number" name="points" class="form-control" min="0" max="100000" value="{{ old('points', 0) }}" required><div class="form-text">Fixed points awarded after submission. No bonus.</div></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Audience</label><select name="audience" id="surveyAudience" class="form-select" required><option value="event_attendees">Attendees of an event</option><option value="public">All residents</option><option value="youth">Youth</option><option value="senior">Senior citizens</option><option value="family_heads">Family heads</option><option value="officials">Officials</option></select></div>
      <div class="col-md-6" id="surveyEventWrap"><label class="form-label fw-semibold">Event</label><select name="event_id" id="surveyEvent" class="form-select" required><option value="">Choose the event attended</option>@foreach ($events as $event)<option value="{{ $event->id }}">{{ $event->title }}{{ $event->event_start_at ? ' · ' . $event->event_start_at->format('M j, Y') : '' }}</option>@endforeach</select></div>
      <div class="col-12"><label class="form-label fw-semibold">Questions</label><div id="surveyQuestions"><div class="input-group mb-2"><span class="input-group-text">1</span><input name="questions[]" class="form-control" placeholder="How well organized was the event?" required></div></div><button type="button" id="addSurveyQuestion" class="btn btn-outline-success btn-sm"><i class="bi bi-plus-circle me-1"></i>Add question</button></div>
      <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="suggestion_enabled" value="1" id="suggestionEnabled" checked><label class="form-check-label" for="suggestionEnabled">Include an optional suggestion box</label></div></div>
    </div>
    <button class="btn btn-yg mt-3"><i class="bi bi-send me-1"></i>Publish survey</button>
  </form>
</div></div>

<div class="card yg-card"><div class="card-header py-3 fw-bold">Published surveys</div><div class="list-group list-group-flush">@forelse ($surveys as $survey)<div class="list-group-item d-flex justify-content-between align-items-center gap-3"><div><div class="fw-semibold">{{ $survey->title }}</div><div class="small text-secondary">{{ count($survey->questions) }} questions · Due {{ $survey->due_at?->format('M j, Y g:i A') }} · {{ $survey->audience === 'event_attendees' ? 'Event attendees' : ucfirst(str_replace('_', ' ', $survey->audience)) }}</div></div><div class="d-flex gap-2"><a class="btn btn-outline-secondary btn-sm" href="#surveySummary{{ $survey->id }}">Summary</a><a class="btn btn-light btn-sm" href="{{ route('surveys.edit', $survey) }}" title="Edit survey"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route('surveys.destroy', $survey) }}" onsubmit="return confirm('Delete this survey and its responses?');">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" title="Delete survey"><i class="bi bi-trash"></i></button></form></div></div>@empty<div class="list-group-item text-secondary">No surveys published yet.</div>@endforelse</div></div>

@foreach ($surveys as $survey)
  @php
    $responses = $survey->responses;
    $allRatings = $responses->flatMap(fn ($response) => $response->answers ?? []);
    $suggestions = $responses->filter(fn ($response) => filled($response->suggestion));
    $respondentDetails = $responses->sortBy('submitted_at')->map(function ($response) use ($survey) {
        return [
            'user' => $response->user?->full_name ?? 'Unknown resident',
            'unique_id' => $response->user?->unique_id ?? '—',
            'submitted_at' => $response->submitted_at?->format('M j, Y g:i A') ?? '—',
            'answers' => $response->answers ?? [],
        ];
    })->values();
  @endphp
  <div class="card yg-card mt-4 survey-summary-print" id="surveySummary{{ $survey->id }}">
    <div class="card-header py-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
      <span class="fw-bold">{{ $survey->title }} summary</span>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge badge-soft">Due {{ $survey->due_at?->format('M j, Y g:i A') }}</span>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="printSurveySummary({{ $survey->id }})">Print summary</button>
      </div>
    </div>
    <div class="card-body p-4">
      <div class="row g-3 mb-4"><div class="col-6 col-md-3"><div class="bg-body-secondary rounded-3 p-3"><div class="h4 fw-bold mb-0">{{ number_format($survey->notified_count) }}</div><div class="small text-secondary">Notified</div></div></div><div class="col-6 col-md-3"><div class="bg-body-secondary rounded-3 p-3"><div class="h4 fw-bold mb-0">{{ $responses->count() }}</div><div class="small text-secondary">Answered</div></div></div><div class="col-6 col-md-3"><div class="bg-body-secondary rounded-3 p-3"><div class="h4 fw-bold mb-0">{{ max(0, $survey->notified_count - $responses->count()) }}</div><div class="small text-secondary">Not answered</div></div></div><div class="col-6 col-md-3"><div class="bg-body-secondary rounded-3 p-3"><div class="h4 fw-bold mb-0">{{ $allRatings->count() ? number_format($allRatings->avg(), 2) : '—' }}</div><div class="small text-secondary">Overall average / 5</div></div></div></div>
      <div class="table-responsive mb-4">
        <table class="table table-sm align-middle border rounded overflow-hidden">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Question</th>
              <th>Average</th>
              <th>Responses</th>
              <th>Strongly Disagree</th>
              <th>Disagree</th>
              <th>Neutral</th>
              <th>Agree</th>
              <th>Strongly Agree</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($survey->questions as $index => $question)
              @php
                $ratings = $responses->map(fn ($response) => $response->answers[$index] ?? null)->filter(fn ($rating) => $rating !== null);
                $distribution = [1 => $ratings->filter(fn ($rating) => (int) $rating === 1)->count(), 2 => $ratings->filter(fn ($rating) => (int) $rating === 2)->count(), 3 => $ratings->filter(fn ($rating) => (int) $rating === 3)->count(), 4 => $ratings->filter(fn ($rating) => (int) $rating === 4)->count(), 5 => $ratings->filter(fn ($rating) => (int) $rating === 5)->count()];
              @endphp
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $question }}</td>
                <td>{{ $ratings->count() ? number_format($ratings->avg(), 2) : '—' }} / 5</td>
                <td>{{ $ratings->count() }}</td>
                <td>{{ $distribution[1] }}</td>
                <td>{{ $distribution[2] }}</td>
                <td>{{ $distribution[3] }}</td>
                <td>{{ $distribution[4] }}</td>
                <td>{{ $distribution[5] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if ($survey->suggestion_enabled)
        <hr><h6 class="fw-bold">Suggestions</h6>
        @forelse ($suggestions as $response)<div class="border-bottom py-2 small">{{ $response->suggestion }}</div>@empty<div class="small text-secondary">No suggestions yet.</div>@endforelse
      @endif

      @if ($respondentDetails->isNotEmpty())
        <hr>
        <h6 class="fw-bold">Survey responses</h6>
        @foreach ($respondentDetails as $respondent)
          <div class="border rounded p-3 mb-3">
            <div class="fw-semibold">{{ $respondent['user'] }}</div>
            <div class="small text-secondary">Unique ID: {{ $respondent['unique_id'] }} · Submitted: {{ $respondent['submitted_at'] }}</div>
            <ul class="mb-0 mt-2 small ps-3">
              @foreach ($survey->questions as $index => $question)
                <li><span class="fw-semibold">{{ $index + 1 }}. {{ $question }}</span> — {{ $respondent['answers'][$index] ?? 'No answer' }} / 5</li>
              @endforeach
            </ul>
          </div>
        @endforeach
      @endif
    </div>
  </div>
@endforeach
@endsection

@push('scripts')
<script>
function printSurveySummary(surveyId) {
  const target = document.getElementById('surveySummary' + surveyId);
  const others = document.querySelectorAll('.survey-summary-print');

  document.body.classList.add('print-mode');
  others.forEach((section) => {
    section.classList.toggle('active', section === target);
  });

  window.print();

  setTimeout(() => {
    document.body.classList.remove('print-mode');
    others.forEach((section) => section.classList.remove('active'));
  }, 500);
}

(() => {
  const audience = document.getElementById('surveyAudience'); const eventWrap = document.getElementById('surveyEventWrap'); const event = document.getElementById('surveyEvent'); const questions = document.getElementById('surveyQuestions'); const add = document.getElementById('addSurveyQuestion');
  if (audience) {
    audience.addEventListener('change', () => { const eventAttendees = audience.value === 'event_attendees'; eventWrap.hidden = !eventAttendees; event.required = eventAttendees; });
  }
  if (add) {
    add.addEventListener('click', () => { const count = questions.children.length + 1; const group = document.createElement('div'); group.className = 'input-group mb-2'; group.innerHTML = `<span class="input-group-text">${count}</span><input name="questions[]" class="form-control" placeholder="How useful was this activity?" required>`; questions.append(group); });
  }
})();
</script>
@endpush

@push('styles')
<style>
  @media print {
    body * { visibility: hidden; }
    .survey-summary-print, .survey-summary-print * { visibility: visible; }
    .survey-summary-print {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      max-width: 100%;
      margin: 0 !important;
      box-shadow: none !important;
      border: none !important;
    }
    body:not(.print-mode) .survey-summary-print { display: none !important; }
    body.print-mode .survey-summary-print:not(.active) { display: none !important; }
    .survey-summary-print .btn,
    .survey-summary-print .d-none-print,
    .survey-summary-print .list-group,
    .survey-summary-print form,
    .survey-summary-print .page-header {
      display: none !important;
    }
  }
</style>
@endpush
