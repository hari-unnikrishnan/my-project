<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicateOwners = DB::table('user_settings')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateOwners) {
            throw new RuntimeException(
                'Cannot add the user settings owner constraint because duplicate settings exist. Resolve them without deleting needed settings, then rerun the migration.'
            );
        }

        Schema::table('user_settings', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
