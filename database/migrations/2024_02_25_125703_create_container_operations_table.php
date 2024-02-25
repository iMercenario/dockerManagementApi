<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContainerOperationsTable extends Migration
{
    public function up()
    {
        Schema::create('container_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained()->onDelete('cascade');
            $table->string('operation_type');
            $table->text('details')->nullable();
            $table->dateTime('timestamp');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('container_operations');
    }
}
