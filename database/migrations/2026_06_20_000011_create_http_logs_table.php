<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('http_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('path');
            $table->string('ip_address')->nullable();
            $table->integer('status');
            $table->float('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('http_logs');
    }
};
