<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('users')->onDelete('cascade');            
            $table->integer('die1_value');
            $table->integer('die2_value');
            $table->enum('result', ['Won', 'Lost']);
            $table->timestamp('roll_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rolls');
    }
};
