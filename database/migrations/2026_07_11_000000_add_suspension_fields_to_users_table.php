<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->nullable()->after('address');
            }

            if (! Schema::hasColumn('users', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('users', 'suspension_type')) {
                $table->string('suspension_type')->nullable()->after('suspended_at');
            }

            if (! Schema::hasColumn('users', 'suspension_value')) {
                $table->unsignedInteger('suspension_value')->nullable()->after('suspension_type');
            }

            if (! Schema::hasColumn('users', 'suspension_reason')) {
                $table->string('suspension_reason')->nullable()->after('suspension_value');
            }

            if (! Schema::hasColumn('users', 'suspension_note')) {
                $table->text('suspension_note')->nullable()->after('suspension_reason');
            }

            if (! Schema::hasColumn('users', 'suspension_ends_at')) {
                $table->timestamp('suspension_ends_at')->nullable()->after('suspension_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'suspension_ends_at')) {
                $table->dropColumn('suspension_ends_at');
            }

            if (Schema::hasColumn('users', 'suspension_note')) {
                $table->dropColumn('suspension_note');
            }

            if (Schema::hasColumn('users', 'suspension_value')) {
                $table->dropColumn('suspension_value');
            }

            if (Schema::hasColumn('users', 'suspension_reason')) {
                $table->dropColumn('suspension_reason');
            }

            if (Schema::hasColumn('users', 'suspension_type')) {
                $table->dropColumn('suspension_type');
            }

            if (Schema::hasColumn('users', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }

            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};