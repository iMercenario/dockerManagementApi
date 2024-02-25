<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_translation_models_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationModelsTable extends Migration
{
    public function up(): void
    {
        Schema::create('translation_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path'); // Шлях до файлу моделі
            $table->boolean('is_active')->default(false); // Прапорець активності моделі
            $table->text('description')->nullable(); // Опис моделі
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_models');
    }
}
