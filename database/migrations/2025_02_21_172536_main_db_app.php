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
        Schema::create('wilayah', function (Blueprint $table) {
            $table->string('kode_kecamatan')->primary();
            $table->string('kecamatan')->unique();
        });

        Schema::create('demografi', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('kode_kecamatan');
            $table->foreign('kode_kecamatan')->references('kode_kecamatan')->on('wilayah');
            $table->year('tahun');
            $table->integer('jumlah_penduduk');
            $table->float('laju_pertumbuhan');
            $table->float('persentase_penduduk');
            $table->float('kepadatan_penduduk');
            $table->float('rasio_jenis_kelamin');
        });

        Schema::create('produksi_tanaman', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('kode_kecamatan');
            $table->foreign('kode_kecamatan')->references('kode_kecamatan')->on('wilayah');
            $table->year('tahun');
            $table->float('produksi_alpukat');
            $table->float('produksi_belimbing');
            $table->float('produksi_duku');
            $table->float('produksi_durian');
            $table->float('produksi_jambu_air');
            $table->float('produksi_jambu_biji');
            $table->float('produksi_jengkol');
            $table->float('produksi_jeruk_besar');
            $table->float('produksi_jeruk_siam');
            $table->float('produksi_mangga');
            $table->float('produksi_manggis');
            $table->float('produksi_nangka');
            $table->float('produksi_nenas');
            $table->float('produksi_pepaya');
            $table->float('produksi_pisang');
            $table->float('produksi_salak');
            $table->float('produksi_sawo');
            $table->float('produksi_sirsak');
            $table->float('produksi_sukun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah');
        Schema::dropIfExists('demografi');
        Schema::dropIfExists('produksi_tanaman');
    }
};
