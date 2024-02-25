<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContainerLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('container_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained()->onDelete('cascade');
            $table->text('log');
            $table->dateTime('timestamp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_logs');
    }
}
