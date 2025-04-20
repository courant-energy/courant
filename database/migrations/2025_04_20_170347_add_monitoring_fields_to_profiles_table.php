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
            $table->boolean('notify_on_anomaly')->default(true)->after('user_id');
            $table->enum('alert_sensitivity', ['low', 'medium', 'high'])->default('medium')->after('notify_on_anomaly');
            $table->json('notification_channels')->nullable()->after('alert_sensitivity');
            $table->float('voltage_threshold_high')->nullable()->after('notification_channels');
            $table->float('voltage_threshold_low')->nullable()->after('voltage_threshold_high');
            $table->float('current_threshold_high')->nullable()->after('voltage_threshold_low');
            $table->float('power_factor_threshold')->nullable()->after('current_threshold_high');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'notify_on_anomaly',
                'alert_sensitivity',
                'notification_channels',
                'voltage_threshold_high',
                'voltage_threshold_low',
                'current_threshold_high',
                'power_factor_threshold'
            ]);
        });
    }
};
