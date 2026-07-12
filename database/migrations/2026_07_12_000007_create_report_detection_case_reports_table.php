<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_detection_case_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_detection_case_id')->constrained('report_detection_cases')->cascadeOnDelete();
            $table->foreignId('animal_report_id')->constrained('animal_reports')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['report_detection_case_id', 'animal_report_id'], 'report_case_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_detection_case_reports');
    }
};