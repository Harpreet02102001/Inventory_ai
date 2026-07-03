{{--
    Alert / Info Box Component

    Renders a colored Bootstrap alert box matching the info callouts in the designs.

    @param string $type  Alert type: 'info' | 'warning' | 'success' | 'danger'
                         Defaults to 'info' (blue box)

    Usage:
        <x-ui.alert type="info">
            Inactive categories will not appear in the product creation dropdown.
        </x-ui.alert>

        <x-ui.alert type="warning">
            Reducing stock will decrease available inventory immediately.
        </x-ui.alert>
--}}

@props(['type' => 'info'])

@php
/**
* Map type to Bootstrap alert class + Bootstrap Icon name.
* Bootstrap alert classes: alert-info, alert-warning, alert-success, alert-danger
*/
[$alertClass, $icon] = match($type) {
'warning' => ['alert-warning', 'bi-exclamation-triangle-fill'],
'success' => ['alert-success', 'bi-check-circle-fill'],
'danger' => ['alert-danger', 'bi-exclamation-circle-fill'],
default => ['alert-info', 'bi-info-circle-fill'],
};
@endphp

<div class="alert {{ $alertClass }} d-flex align-items-start gap-2 py-2" role="alert">
    <i class="bi {{ $icon }} flex-shrink-0 mt-1"></i>
    <div>{{ $slot }}</div>
</div>