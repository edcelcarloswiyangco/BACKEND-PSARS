<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 60)->default('')->after('full_name');
            }

            if (! Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name', 60)->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 60)->default('')->after('middle_name');
            }

            if (! Schema::hasColumn('users', 'suffix')) {
                $table->string('suffix', 10)->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('users', 'country_code')) {
                $table->string('country_code', 8)->default('+63')->after('email');
            }

            if (! Schema::hasColumn('users', 'house_number')) {
                $table->string('house_number', 30)->nullable()->after('contact_number');
            }

            if (! Schema::hasColumn('users', 'building_name')) {
                $table->string('building_name', 120)->nullable()->after('house_number');
            }

            if (! Schema::hasColumn('users', 'street_name')) {
                $table->string('street_name', 120)->default('')->after('building_name');
            }

            if (! Schema::hasColumn('users', 'barangay')) {
                $table->string('barangay', 120)->default('')->after('street_name');
            }

            if (! Schema::hasColumn('users', 'city_municipality')) {
                $table->string('city_municipality', 120)->default('')->after('barangay');
            }

            if (! Schema::hasColumn('users', 'province')) {
                $table->string('province', 120)->default('')->after('city_municipality');
            }

            if (! Schema::hasColumn('users', 'zip_code')) {
                $table->string('zip_code', 10)->default('')->after('province');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'zip_code')) {
                $table->dropColumn('zip_code');
            }

            if (Schema::hasColumn('users', 'province')) {
                $table->dropColumn('province');
            }

            if (Schema::hasColumn('users', 'city_municipality')) {
                $table->dropColumn('city_municipality');
            }

            if (Schema::hasColumn('users', 'barangay')) {
                $table->dropColumn('barangay');
            }

            if (Schema::hasColumn('users', 'street_name')) {
                $table->dropColumn('street_name');
            }

            if (Schema::hasColumn('users', 'building_name')) {
                $table->dropColumn('building_name');
            }

            if (Schema::hasColumn('users', 'house_number')) {
                $table->dropColumn('house_number');
            }

            if (Schema::hasColumn('users', 'country_code')) {
                $table->dropColumn('country_code');
            }

            if (Schema::hasColumn('users', 'suffix')) {
                $table->dropColumn('suffix');
            }

            if (Schema::hasColumn('users', 'last_name')) {
                $table->dropColumn('last_name');
            }

            if (Schema::hasColumn('users', 'middle_name')) {
                $table->dropColumn('middle_name');
            }

            if (Schema::hasColumn('users', 'first_name')) {
                $table->dropColumn('first_name');
            }
        });
    }
};