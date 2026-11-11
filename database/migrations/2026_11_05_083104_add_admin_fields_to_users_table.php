<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
            $table->string('role')->default('user')->after('email');
            $table->string('avatar_url')->nullable()->after('role');
            $table->text('bio')->nullable()->after('avatar_url');
            $table->string('theme')->default('dark')->after('bio');
            $table->boolean('is_admin')->default(false)->after('theme');
            $table->timestamp('last_login_at')->nullable()->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'avatar_url', 'bio', 'theme', 'is_admin', 'last_login_at']);
        });
    }
};
