<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DashboardWidget
 *
 * An immutable value object representing one stat card on the
 * Dashboard. Carries a fixed, typed shape of data from DashboardService
 * to the Blade view — safer than a loose associative array, since a
 * missing or misspelled key here fails immediately (a typed property
 * error) rather than silently rendering blank in the view.
 */
final readonly class DashboardWidget
{
    public function __construct(
        public string $label,
        public int|string $value,
        public string $icon = 'bi-box',
        public ?string $link = null,
        public bool $highlight = false,
    ) {}
}
