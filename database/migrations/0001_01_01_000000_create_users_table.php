<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_users_table
 *
 * Creates the core users table. This table stores authenticated identities only.
 * Role assignment is handled separately via the model_has_roles pivot table,
 * keeping this table clean and the role system flexible.
 *
 * Dependency: none — this is a root table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the users table with status support and removes any assumption
     * of a single hardcoded role column. Role assignment lives in model_has_roles.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                   // Auto-incrementing primary key (BIGINT UNSIGNED)
            $table->string('name');                         // Full display name
            $table->string('email')->unique();              // Login identifier, must be unique
            $table->timestamp('email_verified_at')
                ->nullable();                             // Null = unverified; set by verification flow
            $table->string('password');                     // Bcrypt/Argon2 hash — never plain text
            $table->enum('status', ['active', 'inactive'])
                ->default('active');                      // Allows disabling accounts without deleting them
            $table->rememberToken();                        // 100-char token for "remember me" sessions
            $table->timestamps();                           // created_at and updated_at, managed by Eloquent
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the users table entirely. Foreign key references to this table
     * (model_has_roles, stock_histories, etc.) must be dropped first in their
     * own down() methods, which Laravel handles by running down() in reverse order.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
