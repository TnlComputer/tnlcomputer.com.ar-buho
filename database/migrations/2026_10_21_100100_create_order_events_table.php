<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historial de estados de cada pedido: quién lo cambió (usuario del panel, cliente o Mercado Pago) y cuándo.
        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 20); // admin | cliente | mercadopago | sistema
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
    }
};
