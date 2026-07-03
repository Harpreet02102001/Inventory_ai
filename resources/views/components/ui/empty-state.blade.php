{{--
    Empty State Component

    Shown inside tables when there are no records to display.
    Prevents showing a blank table — gives the user a clear message
    and an optional action to create their first record.

    @param string      $icon        Bootstrap Icons class name e.g. 'bi-grid'
    @param string      $title       Main empty state message
    @param string|null $description Optional supporting text

    Usage:
        <x-ui.empty-state
            icon="bi-grid"
            title="No categories found"
            description="Create your first category to get started."
        >
            <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm mt-3">
<i class="bi bi-plus-lg"></i> Add Category
</a>
</x-ui.empty-state>
--}}

@props(['icon' => 'bi-inbox', 'title', 'description' => null])

<div class="text-center py-5">
    <div
        class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
        style="width: 64px; height: 64px;">
        <i class="bi {{ $icon }} text-secondary fs-3"></i>
    </div>
    <h6 class="fw-semibold text-secondary mb-1">{{ $title }}</h6>
    @if($description)
    <p class="text-muted small mb-0">{{ $description }}</p>
    @endif
    @isset($slot)
    {{ $slot }}
    @endisset
</div>