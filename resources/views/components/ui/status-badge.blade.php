{{--
    Status Badge Component

    Renders a pill-shaped colored badge based on a status string.

    Usage:
    <x-ui.status-badge :status="$category->status" />
--}}

@props(['status'])

@php
/**
* Map status strings to CSS class + label pairs.
*/
[$cssClass, $label] = match (strtolower(str_replace(' ', '_', $status))) {
'active' => ['badge-active', 'Active'],
'inactive' => ['badge-inactive', 'Inactive'],
'low_stock' => ['badge-low-stock', 'Low Stock'],
'add' => ['badge-add', 'Add'],
'reduce' => ['badge-reduce', 'Reduce'],
'purchase' => ['badge-purchase', 'Purchase'],
'sale' => ['badge-sale', 'Sale'],
'adjustment' => ['badge-adjustment', 'Adjustment'],
'draft' => ['badge-draft', 'Draft'],
'ordered' => ['badge-ordered', 'Ordered'],
'received' => ['badge-received', 'Received'],
'cancelled' => ['badge-cancelled', 'Cancelled'],
'confirmed' => ['badge-confirmed', 'Confirmed'],
'confirmed' => ['badge-confirmed', 'Confirmed'],


default => ['badge-inactive', ucfirst($status)],
};
@endphp

<span class="badge {{ $cssClass }}">
    {{ $label }}
</span>