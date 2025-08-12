<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->timestamp('last_turn_on_applied')->nullable()->after('schedule_days');
            $table->timestamp('last_turn_off_applied')->nullable()->after('last_turn_on_applied');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'last_turn_on_applied',
                'last_turn_off_applied'
            ]);
        });
    }
};
