<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('name')->nullable();

            $table->foreignId('telegraph_bot_id')->constrained('bots')->cascadeOnDelete();
            $table->timestamps();
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->unique(['chat_id', 'telegraph_bot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegraph_chats');
    }
};
