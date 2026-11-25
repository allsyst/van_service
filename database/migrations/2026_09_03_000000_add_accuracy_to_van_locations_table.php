<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('van_locations', 'accuracy')) {
            Schema::table('van_locations', function (Blueprint $table) {
                $table->decimal('accuracy', 8, 2)->nullable()->after('longitude');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('van_locations', 'accuracy')) {
            Schema::table('van_locations', function (Blueprint $table) {
                $table->dropColumn('accuracy');
            });
        }
    }
};
