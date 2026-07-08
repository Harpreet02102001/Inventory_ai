<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\DashboardWidget;
use App\Models\User;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StockHistoryRepository;
use App\Repositories\SupplierRepository;
use Illuminate\Support\Collection;

/**
 * DashboardService
 *
 * Maintains a registry of every possible dashboard widget, each tagged
 * with the permission required to see it. getWidgets() filters this
 * registry to only what the requesting user can see — NEVER checking
 * role names, only permissions, so any current or future role (Admin,
 * Staff, or one you haven't created yet) gets the correct dashboard
 * automatically, purely from what's checked in the Roles screen.
 */
class DashboardService
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly SupplierRepository $suppliers,
        private readonly ProductRepository $products,
        private readonly StockHistoryRepository $stockHistories,
    ) {}

    /**
     * The complete registry of every dashboard widget that could ever
     * be shown. To add a new widget later: add one entry here — nothing
     * else in the application needs to change.
     *
     * @return array<int, array{permission: string, resolve: \Closure(): DashboardWidget}>
     */
    private function widgetDefinitions(): array
    {
        return [
            [
                'permission' => 'products.view',
                'resolve' => fn() => new DashboardWidget(
                    label: 'Total Products',
                    value: $this->products->count(),
                    icon: 'bi-box-seam',
                    link: route('products.index'),
                ),
            ],
            [
                'permission' => 'categories.view',
                'resolve' => fn() => new DashboardWidget(
                    label: 'Total Categories',
                    value: $this->categories->count(),
                    icon: 'bi-grid',
                    link: route('categories.index'),
                ),
            ],
            [
                'permission' => 'suppliers.view',
                'resolve' => fn() => new DashboardWidget(
                    label: 'Total Suppliers',
                    value: $this->suppliers->count(),
                    icon: 'bi-truck',
                    link: route('suppliers.index'),
                ),
            ],
            [
                'permission' => 'stock.view',
                'resolve' => function () {
                    $count = $this->products->countLowStock();

                    return new DashboardWidget(
                        label: 'Low Stock Products',
                        value: $count,
                        icon: 'bi-exclamation-triangle',
                        link: route('stock.index', ['low_stock' => 1]),
                        highlight: $count > 0,
                    );
                },
            ],
        ];
    }

    /**
     * Widgets visible to this user, based purely on their permissions.
     *
     * @param User $user
     * @return Collection<int, DashboardWidget>
     */
    public function getWidgets(User $user): Collection
    {
        return collect($this->widgetDefinitions())
            ->filter(fn(array $def) => $user->can($def['permission']))
            ->map(fn(array $def) => ($def['resolve'])())
            ->values();
    }

    /**
     * Recent stock activity across all products, if the user can view
     * stock. Kept separate from the widget registry since it renders
     * as a table, not a stat card.
     *
     * @param User $user
     * @return Collection<int, \App\Models\StockHistory>|null Null if not permitted
     */
    public function getRecentStockActivity(User $user): ?Collection
    {
        return $user->can('stock.view')
            ? $this->stockHistories->getRecent(10)
            : null;
    }
}
