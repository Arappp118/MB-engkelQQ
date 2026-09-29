<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_item_id')->constrained();
            $table->string('item_name_snapshot', 200);
            $table->decimal('price_snapshot', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('service_order_id');
            $table->index('service_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_items');
    }
};
