<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Publicaciones automáticas en redes (tnl:redes): una fila por producto y red, salga bien o mal.
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('network', 20); // facebook | instagram | x
            $table->string('status', 10); // ok | error
            $table->string('remote_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
