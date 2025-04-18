<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crops_production', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->year('year');
            $table->string('province');
            $table->enum('vegetable', ['Bawang Merah', 'Bawang Putih', 'Bawang Daun', 'Kentang', 'Kubis', 'Kembang Kol', 'Petsai/Sawi', 'Wortel', 'Kacang Panjang', 'Cabai Besar', 'Cabai Rawit', 'Tomat', 'Terung', 'Buncis', 'Ketimun', 'Labu Siam', 'Kangkung', 'Bayam', 'Melinjo']);
            $table->double('production');
            $table->double('planted_area');
            $table->double('harvested_area');
            $table->enum('fertilizer_type', ['Urea', 'ZA', 'NPK', 'Organik']);
            $table->double('fertilizer_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crops_production');
    }
};
