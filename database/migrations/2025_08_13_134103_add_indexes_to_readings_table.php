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
        Schema::table('readings', function (Blueprint $table) {
            // Add composite index for profile_id and reading_time (most frequently used together)
            $table->index(['profile_id', 'reading_time'], 'idx_readings_profile_time');
            
            // Add composite index for socket_id and reading_time
            $table->index(['socket_id', 'reading_time'], 'idx_readings_socket_time');
            
            // Add individual index for reading_time for general time-based queries
            $table->index('reading_time', 'idx_readings_time');
            
            // Add index for power column for power-based filtering and calculations
            $table->index('power', 'idx_readings_power');
            
            // Add index for power_factor column for power factor calculations
            $table->index('power_factor', 'idx_readings_power_factor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->dropIndex('idx_readings_profile_time');
            $table->dropIndex('idx_readings_socket_time');
            $table->dropIndex('idx_readings_time');
            $table->dropIndex('idx_readings_power');
            $table->dropIndex('idx_readings_power_factor');
        });
    }
};
