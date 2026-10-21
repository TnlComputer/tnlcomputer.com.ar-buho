<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Para el buscador y el listado del panel cuando haya muchos pedidos.
        Schema::table('orders', function (Blueprint $table) {
            $table->index('email');
            $table->index('created_at');
        });

        // Los pagos son registro contable: la base no deja borrar un pedido que tenga pagos.
        // El número de operación es único por proveedor (Mercado Pago automático o link manual).
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete();
            $table->dropUnique(['provider_id']);
            $table->unique(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_id']);
            $table->unique('provider_id');
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });
    }
};
