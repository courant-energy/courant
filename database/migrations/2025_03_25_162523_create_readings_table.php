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
        Schema::create('readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socket_id')->constrained()->onDelete('cascade');
            $table->float('voltage');
            $table->float('current');
            $table->float('power');
            $table->float('energy');
            $table->float('frequency');
            $table->float('power_factor');
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->dateTime('reading_time')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('readings');
    }
};
