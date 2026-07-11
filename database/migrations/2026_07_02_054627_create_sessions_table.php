<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_sessions_table
 *
 * Creates the sessions table used by Laravel's database session driver.
 * When SESSION_DRIVER=database in .env, Laravel stores all session data
 * here instead of in files — enabling multi-server deployments, admin
 * visibility into active sessions, and forced logout capability.
 *
 * This migration uses the exact structure Laravel's session system expects.
 * Column names and types must match precisely — do not rename them.
 *
 * Dependency: users table must exist first (user_id is a soft reference,
 * not a hard FK — nullable because guests have sessions too).
 */
return new class extends Migration
{
    /**
     * Create the sessions table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();            // Session ID stored in browser cookie
            $table->foreignId('user_id')
                ->nullable()
                ->index();                            // Null for guests, user ID when authenticated
            $table->string('ip_address', 45)
                ->nullable();                         // Supports both IPv4 and IPv6 addresses
            $table->text('user_agent')->nullable();     // Browser/client identifier
            $table->longText('payload');                // Encrypted session data blob
            $table->integer('last_activity')->index();  // Unix timestamp — used for GC and timeout
        });
    }
};
