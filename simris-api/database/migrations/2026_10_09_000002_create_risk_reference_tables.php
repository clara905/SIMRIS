<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Referensi penilaian risiko: skala kemungkinan, dampak, level risiko,
 * area dampak, kriteria dampak, dan kriteria penerimaan risiko.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likelihood_levels', function (Blueprint $table) {
            $table->unsignedTinyInteger('level')->primary();
            $table->string('nama_level', 50);
            $table->string('persentase_kemungkinan', 50)->nullable();
            $table->string('frekuensi_kemungkinan', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('impact_levels', function (Blueprint $table) {
            $table->unsignedTinyInteger('level')->primary();
            $table->string('nama_level', 50);
            $table->timestamps();
        });

        Schema::create('risk_levels', function (Blueprint $table) {
            $table->id('risk_level_id');
            $table->string('nama_level', 50)->unique();
            $table->unsignedTinyInteger('skor_min');
            $table->unsignedTinyInteger('skor_max');
            $table->string('warna', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('area_dampak', function (Blueprint $table) {
            $table->string('kode_area', 20)->primary();
            $table->string('nama_area');
            $table->timestamps();
        });

        Schema::create('impact_criteria', function (Blueprint $table) {
            $table->id('impact_criteria_id');
            $table->string('kode_area', 20);
            $table->unsignedTinyInteger('level');
            $table->text('deskripsi');
            $table->timestamps();

            $table->foreign('kode_area')->references('kode_area')->on('area_dampak')->restrictOnDelete();
            $table->foreign('level')->references('level')->on('impact_levels')->restrictOnDelete();
            $table->unique(['kode_area', 'level']);
        });

        Schema::create('acceptance_criteria', function (Blueprint $table) {
            $table->unsignedTinyInteger('likelihood_level');
            $table->unsignedTinyInteger('impact_level');
            $table->foreignId('risk_level_id')->constrained('risk_levels', 'risk_level_id')->restrictOnDelete();
            $table->enum('rekomendasi_keputusan', ['diterima', 'dimitigasi', 'dihindari', 'dialihkan']);
            $table->timestamps();

            $table->primary(['likelihood_level', 'impact_level']);
            $table->foreign('likelihood_level')->references('level')->on('likelihood_levels')->restrictOnDelete();
            $table->foreign('impact_level')->references('level')->on('impact_levels')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('acceptance_criteria');
        Schema::dropIfExists('impact_criteria');
        Schema::dropIfExists('area_dampak');
        Schema::dropIfExists('risk_levels');
        Schema::dropIfExists('impact_levels');
        Schema::dropIfExists('likelihood_levels');
        Schema::enableForeignKeyConstraints();
    }
};
