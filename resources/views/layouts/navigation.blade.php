{{--
    Navigation Component
    =====================
    Included inside layouts/app.blade.php via @include('layouts.navigation').

    Uses Laravel's @can() / @canany() Blade directives to show or hide
    each navigation item based on the authenticated user's permissions.

    How @can() works here:
      → Blade calls Gate::check('categories.view', $user)
      → Laravel runs our Gate::before() in AuthServiceProvider
      → That loads roles.permissions and calls $user->hasPermission()
      → Returns true/false — Blade shows or hides the link

    As each module is built, replace href="#" with the actual route() call.
    Current placeholder state prevents RouteNotFoundException crashes.
--}}

{{-- ── DASHBOARD ─────────────────────────────────────────────── --}}
@can('dashboard.view')
<a
    href="{{ route('dashboard') }}"
    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i>
    Dashboard
</a>
@endcan

{{-- ── INVENTORY SECTION ────────────────────────────────────────
     Section label only renders if user can see at least one item.
     @canany checks if the user has ANY of the listed permissions.
─────────────────────────────────────────────────────────────── --}}
@canany(['categories.view', 'suppliers.view', 'products.view', 'stock.view'])
<div class="sidebar-section-label">Inventory</div>
@endcanany

@can('categories.view')
<a
    href="#"
    {{-- href="{{ route('categories.index') }}" — uncomment when CategoryController is built --}}
    class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
    <i class="bi bi-grid"></i>
    Categories
</a>
@endcan

@can('suppliers.view')
<a
    href="#"
    {{-- href="{{ route('suppliers.index') }}" — uncomment when SupplierController is built --}}
    class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
    <i class="bi bi-truck"></i>
    Suppliers
</a>
@endcan

@can('products.view')
<a
    href="#"
    {{-- href="{{ route('products.index') }}" — uncomment when ProductController is built --}}
    class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
    <i class="bi bi-box-seam"></i>
    Products
</a>
@endcan

@can('stock.view')
<a
    href="#"
    {{-- href="{{ route('stock.lowstock') }}" — uncomment when StockController is built --}}
    class="nav-link {{ request()->routeIs('stock.lowstock*') ? 'active' : '' }}">
    <i class="bi bi-exclamation-triangle {{ request()->routeIs('stock.lowstock*') ? '' : 'text-warning' }}"></i>
    Low Stock
</a>
@endcan

@can('stock.view')
<a
    href="#"
    {{-- href="{{ route('stock.history') }}" — uncomment when StockController is built --}}
    class="nav-link {{ request()->routeIs('stock.history*') ? 'active' : '' }}">
    <i class="bi bi-clock-history"></i>
    Stock History
</a>
@endcan

{{-- ── MANAGEMENT SECTION ──────────────────────────────────────── --}}
@canany(['users.view', 'roles.view', 'purchase_orders.view', 'sales.view', 'reports.view'])
<div class="sidebar-section-label">Management</div>
@endcanany

@can('users.view')
<a
    href="#"
    {{-- href="{{ route('users.index') }}" — uncomment when UserController is built --}}
    class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i>
    Users
</a>
@endcan

@can('roles.view')
<a
    href="#"
    {{-- href="{{ route('roles.index') }}" — uncomment when RoleController is built --}}
    class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
    <i class="bi bi-shield-check"></i>
    Roles
</a>
@endcan

@can('purchase_orders.view')
<a
    href="#"
    {{-- href="{{ route('purchase-orders.index') }}" — uncomment when PurchaseOrderController is built --}}
    class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
    <i class="bi bi-cart3"></i>
    Purchase Orders
</a>
@endcan

@can('sales.view')
<a
    href="#"
    {{-- href="{{ route('sales.index') }}" — uncomment when SaleController is built --}}
    class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
    <i class="bi bi-receipt"></i>
    Sales
</a>
@endcan

@can('reports.view')
<a
    href="#"
    {{-- href="{{ route('reports.index') }}" — uncomment when ReportController is built --}}
    class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
    <i class="bi bi-bar-chart-line"></i>
    Reports
</a>
@endcan

{{-- ── ACCOUNT SECTION ─────────────────────────────────────────── --}}
<div class="sidebar-section-label">Account</div>

<a
    href="{{ route('profile.edit') }}"
    class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="bi bi-person-circle"></i>
    Profile
</a>