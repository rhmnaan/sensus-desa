<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_kesejahteraan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pendataan_id')->unique()->constrained('pendataan')->restrictOnDelete();
            $table->decimal('skor', 8, 2);
            $table->text('catatan')->nullable();
            $table->dateTime('dihitung_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_kesejahteraan');
    }
};
