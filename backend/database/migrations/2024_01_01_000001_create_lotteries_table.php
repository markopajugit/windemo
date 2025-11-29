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
        Schema::create('lotteries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('product_value', 10, 2);
            $table->decimal('ticket_price', 10, 2);
            $table->unsignedInteger('total_tickets');
            $table->decimal('charity_percentage', 5, 2)->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['pending', 'approved', 'active', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'ends_at']);
            $table->index('starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotteries');
    }
};

