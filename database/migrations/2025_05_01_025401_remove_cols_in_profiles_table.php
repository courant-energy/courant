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
            // voltage_threshold_high,voltage_threshold_low,current_threshold_high,power_factor_threshold

            // Remove the columns from the profiles table
            $table->dropColumn([
                'voltage_threshold_high',
                'voltage_threshold_low',
                'current_threshold_high',
                'power_factor_threshold',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Add the columns back to the profiles table
            $table->float('voltage_threshold_high')->nullable()->after('notification_channels');
            $table->float('voltage_threshold_low')->nullable()->after('voltage_threshold_high');
            $table->float('current_threshold_high')->nullable()->after('voltage_threshold_low');
            $table->float('power_factor_threshold')->nullable()->after('current_threshold_high');
        });
    }
};
