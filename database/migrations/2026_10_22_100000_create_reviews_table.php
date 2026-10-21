<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reseñas de productos. Solo las deja quien compró (desde la página de su pedido entregado) y se publican
        // recién cuando se aprueban en el panel.
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('author', 60);           // "Ana P.": nombre y la inicial del apellido
            $table->unsignedTinyInteger('rating');  // 1 a 5
            $table->text('comment')->nullable();
            $table->string('status', 10)->default('pending')->index(); // pending | approved | rejected
            $table->string('ip', 45)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
