<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['2_tak', '4_tak', 'kelistrikan', 'umum', 'sparepart']);
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('unit', 50)->default('item');
            $table->integer('stock')->nullable()->comment('null = jasa/tidak ada stok');
            $table->boolean('is_sparepart')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index(['category', 'is_active']);
            $table->index('is_sparepart');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
