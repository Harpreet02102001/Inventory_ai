<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * AppLayout Component
 *
 * Legacy Breeze component kept for compatibility.
 * New views use @extends('layouts.app') directly instead.
 *
 * This component is no longer used by our new views but kept
 * to prevent class-not-found errors if any old Breeze views
 * still reference it during the transition.
 */
class AppLayout extends Component
{
    /**
     * Render the component.
     *
     * Returns the layouts/app view — our new Bootstrap 5 layout.
     * Note: Component slot ($slot) won't appear in @yield('content')
     * so any remaining Breeze views using <x-app-layout> should be
     * converted to @extends('layouts.app') as we build each module.
     *
     * @return View
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
