<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->timestamp('departed_at')->nullable()->after('departure_time');
            $table->timestamp('departure_notified_at')->nullable()->after('departed_at');
            $table->timestamp('delay_notified_at')->nullable()->after('departure_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['departed_at', 'departure_notified_at', 'delay_notified_at']);
        });
    }
};
