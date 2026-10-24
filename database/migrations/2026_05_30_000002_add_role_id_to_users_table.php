<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained('roles')->nullOnDelete();
        });

        $roles = DB::table('roles')->pluck('id', 'slug')->all();

        if (isset($roles['pending'])) {
            DB::table('users')->update(['role_id' => $roles['pending']]);
        }

        if (Schema::hasColumn('users', 'role')) {
            $users = DB::table('users')->select('id', 'role')->get();
            foreach ($users as $user) {
                if (isset($user->role) && isset($roles[$user->role])) {
                    DB::table('users')->where('id', $user->id)->update(['role_id' => $roles[$user->role]]);
                }
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('email');
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
