{{--
    Page Header Component

    Renders the standard page title + subtitle + optional action button
    used at the top of every list page in the designs.

    @param string      $title     The main page heading
    @param string|null $subtitle  Optional descriptive subtitle

    Usage:
        <x-ui.page-header title="Categories" subtitle="View and manage product categories">
            <x-slot:action>
                <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">
<i class="bi bi-plus-lg"></i> Add Category
</a>
</x-slot:action>
</x-ui.page-header>
--}}

@props(['title', 'subtitle' => null])

<div class="page-header">
    <div>
        <h1>{{ $title }}</h1>
        @if($subtitle)
        <p>{{ $subtitle }}</p>
        @endif
    </div>

    @isset($action)
    <div>{{ $action }}</div>
    @endisset
</div>