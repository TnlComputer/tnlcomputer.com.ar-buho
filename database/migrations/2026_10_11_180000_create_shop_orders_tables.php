<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('eta')->nullable();      // "24 a 48 h hábiles"
            $table->string('eta_en')->nullable();
            // Rangos de código postal, uno por línea: "1000-1499". Vacío = zona "resto del país".
            $table->text('postal_ranges')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('free_from', 12, 2)->nullable(); // envío gratis desde este subtotal
            $table->boolean('is_pickup')->default(false);    // retiro en el local
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique(); // link público del pedido; no se puede adivinar
            $table->string('status', 20)->index();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('dni', 20)->nullable();
            $table->foreignId('shipping_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_zone_name')->nullable(); // copia: si la zona cambia, el pedido no
            $table->string('postal_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('ARS');
            $table->string('mp_preference_id')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('locale', 5)->default('es');
            $table->string('ip', 45)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedSmallInteger('qty');
            $table->decimal('subtotal', 12, 2);
        });

        // Un pedido puede tener varios pagos (ej. dos tarjetas). Se guardan tal como los informa Mercado Pago.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 20)->default('mercadopago');
            $table->string('provider_id')->unique();
            $table->string('status', 30);          // approved, pending, in_process, rejected, refunded...
            $table->string('status_detail')->nullable();
            $table->string('payment_type')->nullable();   // credit_card, debit_card, account_money...
            $table->string('payment_method')->nullable(); // visa, master, debvisa...
            $table->string('card_last_four', 4)->nullable();
            $table->unsignedSmallInteger('installments')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('amount_refunded', 12, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shipping_zones');
    }
};
