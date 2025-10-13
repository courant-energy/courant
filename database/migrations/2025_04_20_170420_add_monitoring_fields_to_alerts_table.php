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
        Schema::table('alerts', function (Blueprint $table) {
            // Add new columns after 'message'
            $table->json('data')->nullable()->after('message');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info')->after('data');
            $table->timestamp('resolved_at')->nullable()->after('resolved');
            $table->text('resolution_notes')->nullable()->after('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn([
                'data',
                'severity',
                'resolved_at',
                'resolution_notes',
            ]);
        });
    }
};
