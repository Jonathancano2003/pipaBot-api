<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('resumen');
            $table->longText('mensajes')->nullable(); // ✅ NUEVO para guardar todos los mensajes
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_histories');
    }
}
