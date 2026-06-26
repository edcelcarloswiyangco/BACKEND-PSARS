<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('registration_year')->nullable()->after('id');
            $table->unsignedInteger('registration_sequence')->nullable()->after('registration_year');
            $table->unique(['registration_year', 'registration_sequence'], 'users_registration_year_sequence_unique');
        });

        $users = DB::table('users')
            ->select('id', 'created_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $sequenceByYear = [];

        foreach ($users as $user) {
            $year = $user->created_at
                ? (int) Carbon::parse($user->created_at)->format('Y')
                : (int) now()->format('Y');

            $sequenceByYear[$year] = ($sequenceByYear[$year] ?? 0) + 1;

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'registration_year' => $year,
                    'registration_sequence' => $sequenceByYear[$year],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_registration_year_sequence_unique');
            $table->dropColumn(['registration_year', 'registration_sequence']);
        });
    }
};