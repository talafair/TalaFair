<div class="mb-3">
    <label for="annTitle{{ $suffix }}" class="form-label fw-semibold">Title</label>
    <input type="text" name="title" id="annTitle{{ $suffix }}" class="form-control yg-input"
           value="{{ $announcement->title ?? '' }}" maxlength="255" required>
</div>

<div class="mb-3">
    <label for="annCategory{{ $suffix }}" class="form-label fw-semibold">Category</label>
    <select name="category" id="annCategory{{ $suffix }}" class="form-select yg-input" required>
        @foreach (['events' => 'Events', 'ice_breaker' => 'Ice Breaker', 'q_and_a' => 'Q&A', 'game' => 'Game', 'intermission' => 'Intermission', 'updates' => 'Updates', 'rewards' => 'Rewards', 'maintenance' => 'Maintenance'] as $value => $label)
            <option value="{{ $value }}" {{ ($announcement->category ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="annBody{{ $suffix }}" class="form-label fw-semibold">Content</label>
    <textarea name="body" id="annBody{{ $suffix }}" class="form-control yg-input" rows="5"
              maxlength="5000" required>{{ $announcement->body ?? '' }}</textarea>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="annFeatured{{ $suffix }}"
           {{ ($announcement->is_featured ?? false) ? 'checked' : '' }}>
    <label class="form-check-label fw-semibold small" for="annFeatured{{ $suffix }}">
        Feature this announcement (shown in the banner — replaces the current featured one)
    </label>
</div>

<div class="mb-3">
    <label for="annBanner{{ $suffix }}" class="form-label fw-semibold">Banner image <span class="text-secondary fw-normal">(optional)</span></label>
    <input type="file" name="banner" id="annBanner{{ $suffix }}" class="form-control yg-input"
           accept="image/jpeg,image/png,image/webp">
    @if (($announcement->banner_path ?? null))
        <div class="form-text">Leave empty to keep the current image.</div>
    @endif
</div>

@include('pages.partials.event-fields', ['announcement' => $announcement ?? null, 'suffix' => $suffix])
