<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The registration form presents address, postal code and city as
     * optional, but the original users migration made them NOT NULL, so
     * registering without them failed with a database error.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
            $table->string('city')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable(false)->change();
            $table->string('postal_code')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
        });
    }
};
