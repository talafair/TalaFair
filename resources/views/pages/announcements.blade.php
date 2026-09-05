@extends('layouts.app')

@section('title', 'Announcements')

@section('content')

<div class="announcement-page">
    @php
        $categoryStyles = [
            'events' => ['badge' => 'badge-soft', 'icon' => 'bi-calendar-event'],
            'updates' => ['badge' => 'badge-yg', 'icon' => 'bi-rocket-takeoff'],
            'rewards' => ['badge' => 'badge-gold', 'icon' => 'bi-gift'],
            'maintenance' => ['badge' => 'bg-secondary-subtle text-secondary', 'icon' => 'bi-tools'],
            'ice_breaker' => ['badge' => 'badge-gold', 'icon' => 'bi-lightbulb'],
            'q_and_a' => ['badge' => 'badge-yg', 'icon' => 'bi-question-circle'],
            'game' => ['badge' => 'badge-soft', 'icon' => 'bi-controller'],
            'intermission' => ['badge' => 'badge-gold', 'icon' => 'bi-music-note-beamed'],
        ];
    @endphp

    @php $isOfficial = auth()->user()->isOfficial(); @endphp

    @if ($isOfficial)
        <div class="d-flex justify-content-end mb-4">
            <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                <i class="bi bi-plus-circle me-2"></i>New Announcement
            </button>
        </div>
    @endif

    {{-- Featured announcement --}}
    @if ($featured)
        <div class="featured-banner p-4 p-lg-5 mb-4">
            <span class="deco" style="width: 260px; height: 260px; top: -100px; right: -70px;"></span>
            <span class="deco" style="width: 140px; height: 140px; bottom: -60px; right: 180px;"></span>
            <div class="row align-items-center position-relative" style="z-index: 1;">
                <div class="col-lg-8">
                    <span class="badge bg-white text-yg rounded-pill px-3 py-2 mb-3 fw-bold">
                        <i class="bi bi-pin-angle-fill me-1"></i>Featured
                    </span>
                    @if ($featured->eventStatus())
                        <span class="badge {{ $featured->eventStatus() === 'open' ? 'bg-success-subtle text-success-emphasis' : ($featured->eventStatus() === 'soon' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-secondary-subtle text-secondary') }} rounded-pill px-3 py-2 mb-3 ms-1">{{ ucfirst($featured->eventStatus()) }}</span>
                    @endif
                    <h2 class="fw-bold mb-2">{{ $featured->title }}</h2>
                    <p class="mb-4 opacity-75" style="max-width: 34rem;">{{ Str::limit($featured->body, 180) }}</p>
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('announcements.show', $featured) }}" class="btn btn-light fw-bold rounded-3 px-4">
                            Read More <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <span class="small fw-semibold opacity-75">
                            <i class="bi bi-calendar3 me-1"></i>{{ $featured->created_at->format('M j, Y') }}
                        </span>
                    </div>
                    @if ($isOfficial)
                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-light btn-sm fw-semibold rounded-3" data-bs-toggle="modal"
                                    data-bs-target="#editAnnouncementModal{{ $featured->id }}">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <form action="{{ route('announcements.destroy', $featured) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this announcement?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-dark btn-sm fw-semibold rounded-3">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="col-lg-4 d-none d-lg-block text-center">
                    <i class="bi bi-megaphone-fill" style="font-size: 7rem; opacity: .5;"></i>
                </div>
            </div>
        </div>
    @endif

    {{-- Search + filters --}}
    <div class="card yg-card p-3 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-5">
                <div class="input-group">
                    <span class="input-group-text yg-addon"><i class="bi bi-search"></i></span>
                    <input type="search" id="announcementSearch" class="form-control yg-input"
                           placeholder="Search announcements..." aria-label="Search announcements">
                </div>
            </div>
            <div class="col-lg-7 d-flex justify-content-lg-end">
                <div class="dropdown">
                    <button class="btn filter-dropdown-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            data-bs-auto-close="outside" aria-expanded="false" aria-label="Filter announcements">
                        <i class="bi bi-funnel me-2"></i><span id="announcementFilterLabel">All announcements</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 announcement-filter-menu">
                        <label for="announcementCategoryFilter" class="form-label small fw-semibold text-secondary mb-1">Category</label>
                        <select id="announcementCategoryFilter" class="form-select mb-3">
                            <option value="all">All categories</option>
                            <option value="events">Events</option>
                            <option value="updates">Updates</option>
                            <option value="rewards">Rewards</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="ice_breaker">Ice Breaker</option>
                            <option value="q_and_a">Q&amp;A</option>
                            <option value="game">Game</option>
                            <option value="intermission">Intermission</option>
                        </select>
                        <label for="announcementStatusFilter" class="form-label small fw-semibold text-secondary mb-1">Event status</label>
                        <select id="announcementStatusFilter" class="form-select">
                            <option value="all">All event status</option>
                            <option value="soon">Soon</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Announcement cards --}}
    <div class="mb-3" aria-label="Announcements">
    <div class="row g-4" id="announcementGrid">
        @forelse ($announcements as $announcement)
            @php $style = $categoryStyles[$announcement->category] ?? ['badge' => 'badge-soft', 'icon' => 'bi-megaphone']; @endphp
            <div class="col-md-6 col-xl-4 announcement-item" data-category="{{ $announcement->category }}" data-event-status="{{ $announcement->eventStatus() ?? 'none' }}">
                <div class="card yg-card yg-card-hover h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge {{ $style['badge'] }} rounded-pill px-3">
                                <i class="bi {{ $style['icon'] }} me-1"></i>{{ ['ice_breaker' => 'Ice Breaker', 'q_and_a' => 'Q&A', 'intermission' => 'Intermission'][$announcement->category] ?? ucfirst($announcement->category) }}
                            </span>
                            <span class="small text-secondary">{{ $announcement->created_at->format('M j, Y') }}</span>
                        </div>
                        @if ($announcement->eventStatus())
                            <span class="badge {{ $announcement->eventStatus() === 'open' ? 'bg-success-subtle text-success-emphasis' : ($announcement->eventStatus() === 'soon' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-secondary-subtle text-secondary') }} rounded-pill align-self-start mb-2">
                                <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle;"></i>{{ ucfirst($announcement->eventStatus()) }}
                            </span>
                        @endif
                        <h5 class="fw-bold">{{ $announcement->title }}</h5>
                        <p class="text-secondary small flex-grow-1">{{ Str::limit($announcement->body, 140) }}</p>
                        @if ($announcement->is_event)
                            <div class="small text-secondary mb-3">
                                <div><i class="bi bi-calendar-event me-1 text-yg"></i>{{ $announcement->event_start_at?->format('M j, Y g:i A') }}</div>
                                <div><i class="bi bi-geo-alt me-1 text-yg"></i>{{ $announcement->venue_name ?: 'Barangay San Jose' }}</div>
                                <div class="fw-semibold text-yg mt-1">
                                    @if ($announcement->isParticipationActivity())
                                        {{ $announcement->participation_points }} participation points
                                    @else
                                        {{ $announcement->base_points }} base points + 10% early bonus
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('announcements.show', $announcement) }}" class="btn btn-yg-outline btn-sm">
                                Read More <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                            @if ($isOfficial)
                                <button class="btn btn-light btn-sm rounded-3 ms-auto" title="Edit" data-bs-toggle="modal"
                                        data-bs-target="#editAnnouncementModal{{ $announcement->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete “{{ $announcement->title }}”?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 announcement-item announcement-empty">
                <div class="card yg-card text-center p-5">
                    <i class="bi bi-megaphone text-secondary" style="font-size: 3.5rem;"></i>
                    <h4 class="fw-bold mt-3">No announcements yet</h4>
                    <p class="text-secondary mb-0">Check back soon for news and updates.</p>
                </div>
            </div>
        @endforelse
      </div>
    </div>

    <p id="noResults" class="text-center text-secondary py-5 d-none">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>No announcements match your search.
    </p>

    {{-- Pagination --}}
    @if ($announcements->hasPages())
        <nav aria-label="Announcements pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item {{ $announcements->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $announcements->previousPageUrl() ?? '#' }}" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
                </li>
                @foreach ($announcements->getUrlRange(1, $announcements->lastPage()) as $page => $url)
                    <li class="page-item {{ $page === $announcements->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach
                <li class="page-item {{ $announcements->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $announcements->nextPageUrl() ?? '#' }}" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
    @endif

    {{-- Read-more modals (everyone) --}}
    @foreach (collect([$featured])->filter()->concat($announcements) as $item)
        @php $style = $categoryStyles[$item->category] ?? ['badge' => 'badge-soft', 'icon' => 'bi-megaphone']; @endphp
        <div class="modal fade" id="viewAnnouncementModal{{ $item->id }}" tabindex="-1"
             aria-labelledby="viewAnnouncementLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge {{ $style['badge'] }} rounded-pill px-3">
                                    <i class="bi {{ $style['icon'] }} me-1"></i>{{ ['ice_breaker' => 'Ice Breaker', 'q_and_a' => 'Q&A', 'intermission' => 'Intermission'][$item->category] ?? ucfirst($item->category) }}
                                </span>
                                @if ($item->is_featured)
                                    <span class="badge badge-gold rounded-pill px-3"><i class="bi bi-pin-angle-fill me-1"></i>Featured</span>
                                @endif
                                <span class="small text-secondary">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $item->created_at->format('F j, Y · g:i A') }}
                                </span>
                            </div>
                            <h4 class="modal-title fw-bold" id="viewAnnouncementLabel{{ $item->id }}">{{ $item->title }}</h4>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 pb-4">
                        @include('pages.partials.announcement-details', ['announcement' => $item])
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-yg px-4" data-bs-dismiss="modal">
                            <i class="bi bi-check-circle me-2"></i>Got it
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if ($isOfficial)
        @if ($errors->any())
            <div class="alert alert-danger rounded-3 mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        {{-- Add announcement modal --}}
        <div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0" style="border-radius: 20px;">
                    <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="addAnnouncementLabel"><i class="bi bi-plus-circle me-2 text-yg"></i>New Announcement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            @include('pages.partials.announcement-fields', ['announcement' => null, 'suffix' => 'add'])
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light fw-semibold rounded-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-yg"><i class="bi bi-megaphone me-2"></i>Publish</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit announcement modals --}}
        @foreach (collect([$featured])->filter()->concat($announcements) as $item)
            <div class="modal fade" id="editAnnouncementModal{{ $item->id }}" tabindex="-1"
                 aria-labelledby="editAnnouncementLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0" style="border-radius: 20px;">
                        <form action="{{ route('announcements.update', $item) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold" id="editAnnouncementLabel{{ $item->id }}"><i class="bi bi-pencil-square me-2 text-yg"></i>Edit Announcement</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                @include('pages.partials.announcement-fields', ['announcement' => $item, 'suffix' => $item->id])
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light fw-semibold rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-yg"><i class="bi bi-check-circle me-2"></i>Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

</div>

@endsection

@push('styles')
<style>
    .announcement-item > .card { min-height: 100%; }
    .filter-dropdown-toggle { background: #fff; border: 2px solid var(--yg-primary); color: var(--yg-secondary); font-weight: 600; border-radius: 50rem; padding: .45rem 1.15rem; }
    .filter-dropdown-toggle:hover, .filter-dropdown-toggle:focus { background: var(--yg-primary); color: var(--yg-ink); }
    .announcement-filter-menu { min-width: 15rem; border: 0; border-radius: 1rem; box-shadow: 0 10px 30px rgba(52, 58, 64, .14); }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const search = document.getElementById('announcementSearch');
        const categoryFilter = document.getElementById('announcementCategoryFilter');
        const statusFilter = document.getElementById('announcementStatusFilter');
        const filterLabel = document.getElementById('announcementFilterLabel');
        const items = document.querySelectorAll('.announcement-item');
        const noResults = document.getElementById('noResults');
        let activeFilter = 'all';
        let activeStatus = 'all';

        function applyFilters() {
            const term = search.value.trim().toLowerCase();
            let visible = 0;
            items.forEach(item => {
                const matchesCategory = activeFilter === 'all' || item.dataset.category === activeFilter;
                const matchesStatus = activeStatus === 'all' || item.dataset.eventStatus === activeStatus;
                const matchesTerm = !term || item.textContent.toLowerCase().includes(term);
                const show = matchesCategory && matchesStatus && matchesTerm;
                item.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            if (items.length > 0) noResults.classList.toggle('d-none', visible > 0);
        }

        function updateFilterLabel() {
            const categoryLabel = categoryFilter.options[categoryFilter.selectedIndex].text;
            const statusLabel = statusFilter.options[statusFilter.selectedIndex].text;
            filterLabel.textContent = activeFilter === 'all' && activeStatus === 'all'
                ? 'All announcements'
                : activeFilter !== 'all' && activeStatus !== 'all'
                    ? `${categoryLabel} · ${statusLabel}`
                    : activeFilter !== 'all' ? categoryLabel : statusLabel;
        }

        categoryFilter.addEventListener('change', () => {
            activeFilter = categoryFilter.value;
            updateFilterLabel();
            applyFilters();
        });

        statusFilter.addEventListener('change', () => {
            activeStatus = statusFilter.value;
            updateFilterLabel();
            applyFilters();
        });

            search.addEventListener('input', applyFilters);
        })();

        </script>
        @endpush

        {{-- Geolocation for the event venue pin (add + edit modals) --}}
        @push('scripts')
        <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-use-my-location');
            if (! btn) return;

            if (! navigator.geolocation) {
                alert('Your browser does not support location.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (p) {
                    document.getElementById(btn.dataset.lat).value = p.coords.latitude.toFixed(7);
                    document.getElementById(btn.dataset.lng).value = p.coords.longitude.toFixed(7);
                },
                function () {
                    alert('Could not read your location. Allow location access and try again.');
                }
            );
        });
        </script>
        @endpush
