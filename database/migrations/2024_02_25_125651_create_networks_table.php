<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworksTable extends Migration
{
    public function up(): void
    {
        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->string('docker_network_id')->unique();
            $table->string('name');
            $table->string('driver');
            $table->string('scope');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('networks');
    }
}
