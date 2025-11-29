<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lottery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['lottery_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottery_images');
    }
};

