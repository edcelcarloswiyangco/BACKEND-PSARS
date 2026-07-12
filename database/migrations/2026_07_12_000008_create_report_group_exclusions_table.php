<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_group_exclusions', function (Blueprint $table) {
            $table->id();
            $table->string('group_type', 32);
            $table->string('group_key');
            $table->foreignId('report_id')->constrained('animal_reports')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_type', 'group_key', 'report_id'], 'report_group_exclusion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_group_exclusions');
    }
};