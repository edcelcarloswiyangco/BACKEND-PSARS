<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_detection_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('report_type', 50);
            $table->string('animal_type', 100);
            $table->string('matching_state', 32)->default('open_for_matching');
            $table->timestamp('matching_window_started_at')->nullable();
            $table->timestamp('matching_window_ends_at')->nullable();
            $table->string('primary_location_text')->nullable();
            $table->decimal('center_latitude', 10, 7)->nullable();
            $table->decimal('center_longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_detection_cases');
    }
};