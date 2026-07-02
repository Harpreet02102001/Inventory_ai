<?php

/**
 * Permissions Configuration
 *
 * This is the single source of truth for all permissions in the system.
 * Format: 'group_name' => [ ['name' => 'module.action', 'display_name' => '...'], ... ]
 *
 * To add a new permission:
 *   1. Add it here
 *   2. Run: php artisan db:seed --class=PermissionSeeder
 *   3. Assign it to the appropriate role via RoleSeeder or the UI
 *
 * Naming convention: module.action (lowercase, dot-separated)
 */

return [

    'User Management' => [
        ['name' => 'users.view',   'display_name' => 'View Users'],
        ['name' => 'users.create', 'display_name' => 'Create Users'],
        ['name' => 'users.edit',   'display_name' => 'Edit Users'],
        ['name' => 'users.delete', 'display_name' => 'Delete Users'],
    ],

    'Role Management' => [
        ['name' => 'roles.view',   'display_name' => 'View Roles'],
        ['name' => 'roles.create', 'display_name' => 'Create Roles'],
        ['name' => 'roles.edit',   'display_name' => 'Edit Roles'],
        ['name' => 'roles.delete', 'display_name' => 'Delete Roles'],
    ],

    'Categories' => [
        ['name' => 'categories.view',   'display_name' => 'View Categories'],
        ['name' => 'categories.create', 'display_name' => 'Create Categories'],
        ['name' => 'categories.edit',   'display_name' => 'Edit Categories'],
        ['name' => 'categories.delete', 'display_name' => 'Delete Categories'],
    ],

    'Suppliers' => [
        ['name' => 'suppliers.view',   'display_name' => 'View Suppliers'],
        ['name' => 'suppliers.create', 'display_name' => 'Create Suppliers'],
        ['name' => 'suppliers.edit',   'display_name' => 'Edit Suppliers'],
        ['name' => 'suppliers.delete', 'display_name' => 'Delete Suppliers'],
    ],

    'Products' => [
        ['name' => 'products.view',   'display_name' => 'View Products'],
        ['name' => 'products.create', 'display_name' => 'Create Products'],
        ['name' => 'products.edit',   'display_name' => 'Edit Products'],
        ['name' => 'products.delete', 'display_name' => 'Delete Products'],
    ],

    'Stock' => [
        ['name' => 'stock.view',   'display_name' => 'View Stock'],
        ['name' => 'stock.adjust', 'display_name' => 'Adjust Stock'],
    ],

    'Purchase Orders' => [
        ['name' => 'purchase_orders.view',    'display_name' => 'View Purchase Orders'],
        ['name' => 'purchase_orders.create',  'display_name' => 'Create Purchase Orders'],
        ['name' => 'purchase_orders.edit',    'display_name' => 'Edit Purchase Orders'],
        ['name' => 'purchase_orders.approve', 'display_name' => 'Approve Purchase Orders'],
        ['name' => 'purchase_orders.cancel',  'display_name' => 'Cancel Purchase Orders'],
    ],

    'Sales' => [
        ['name' => 'sales.view',   'display_name' => 'View Sales'],
        ['name' => 'sales.create', 'display_name' => 'Create Sales'],
        ['name' => 'sales.edit',   'display_name' => 'Edit Sales'],
        ['name' => 'sales.cancel', 'display_name' => 'Cancel Sales'],
    ],

    'Reports' => [
        ['name' => 'reports.view',   'display_name' => 'View Reports'],
        ['name' => 'reports.export', 'display_name' => 'Export Reports'],
    ],

    'Dashboard' => [
        ['name' => 'dashboard.view', 'display_name' => 'View Dashboard'],
    ],

];
