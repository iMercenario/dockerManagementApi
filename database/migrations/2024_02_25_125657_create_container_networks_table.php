<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContainerNetworksTable extends Migration
{
    public function up(): void
    {
        Schema::create('container_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained()->onDelete('cascade');
            $table->foreignId('network_id')->constrained()->onDelete('cascade');
            $table->string('ip_address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_networks');
    }
}
