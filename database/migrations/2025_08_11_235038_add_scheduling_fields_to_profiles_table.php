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
            $table->boolean('scheduling_enabled')->default(false)->after('location');
            $table->time('turn_on_time')->nullable()->after('scheduling_enabled');
            $table->time('turn_off_time')->nullable()->after('turn_on_time');
            $table->json('schedule_days')->nullable()->after('turn_off_time'); // Days of week: ['monday', 'tuesday', etc.]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'scheduling_enabled',
                'turn_on_time',
                'turn_off_time',
                'schedule_days'
            ]);
        });
    }
};
