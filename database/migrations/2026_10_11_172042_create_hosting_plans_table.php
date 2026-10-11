<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('panel', 20); // directadmin | cpanel
            $table->unsignedSmallInteger('domains')->default(1);
            $table->string('disk');
            $table->string('transfer');
            $table->decimal('price_monthly', 12, 2);
            $table->decimal('price_semester', 12, 2)->nullable();
            $table->decimal('price_annual', 12, 2)->nullable();
            $table->decimal('price_biannual', 12, 2)->nullable();
            $table->boolean('highlighted')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_plans');
    }
};
