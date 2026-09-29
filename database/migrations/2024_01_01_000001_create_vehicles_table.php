<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_polisi', 20)->unique();
            $table->string('merk', 100);
            $table->string('model', 100);
            $table->string('tahun', 4);
            $table->enum('tipe_mesin', ['2_tak', '4_tak', 'listrik']);
            $table->enum('transmisi', ['manual', 'matic', 'semi_matic'])->default('manual');
            $table->string('warna', 50)->nullable();
            $table->string('nomor_rangka', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('tipe_mesin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
