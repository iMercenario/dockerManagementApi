<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContainersTable extends Migration
{
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('docker_id')->unique();
            $table->string('name');
            $table->string('image');
            $table->dateTime('creation_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('containers');
    }
}
