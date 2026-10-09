<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Library: ancaman, kerentanan, kontrol keamanan, SoA, regulasi, SOP, rencana kerja.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Ancaman ----
        Schema::create('threat_types', function (Blueprint $table) {
            $table->string('kode_jenis_ancaman', 20)->primary();
            $table->string('jenis_ancaman');
            $table->timestamps();
        });

        Schema::create('threats', function (Blueprint $table) {
            $table->string('kode_ancaman', 20)->primary();
            $table->string('kode_jenis_ancaman', 20);
            $table->string('ancaman');
            $table->timestamps();

            $table->foreign('kode_jenis_ancaman')->references('kode_jenis_ancaman')->on('threat_types')->restrictOnDelete();
        });

        Schema::create('threat_sources', function (Blueprint $table) {
            $table->string('kode_sumber', 20)->primary();
            $table->string('sumber_ancaman');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('threat_source_relations', function (Blueprint $table) {
            $table->id('threat_source_relations_id');
            $table->string('kode_ancaman', 20);
            $table->string('kode_sumber', 20);
            $table->timestamps();

            $table->foreign('kode_ancaman')->references('kode_ancaman')->on('threats')->cascadeOnDelete();
            $table->foreign('kode_sumber')->references('kode_sumber')->on('threat_sources')->cascadeOnDelete();
            $table->unique(['kode_ancaman', 'kode_sumber']);
        });

        // ---- Kerentanan ----
        Schema::create('vulnerability_types', function (Blueprint $table) {
            $table->string('kode_jenis_kerentanan', 20)->primary();
            $table->string('jenis_kerentanan');
            $table->timestamps();
        });

        Schema::create('vulnerabilities', function (Blueprint $table) {
            $table->string('kode_kerentanan', 20)->primary();
            $table->string('kode_jenis_kerentanan', 20);
            $table->string('kerentanan');
            $table->timestamps();

            $table->foreign('kode_jenis_kerentanan')->references('kode_jenis_kerentanan')->on('vulnerability_types')->restrictOnDelete();
        });

        // ---- Kontrol keamanan & SoA ----
        Schema::create('control_domains', function (Blueprint $table) {
            $table->id('control_domain_id');
            $table->string('kode', 30)->unique();
            $table->string('nama_domain');
            $table->timestamps();
        });

        Schema::create('control_objectives', function (Blueprint $table) {
            $table->id('control_objective_id');
            $table->foreignId('control_domain_id')->constrained('control_domains', 'control_domain_id')->restrictOnDelete();
            $table->string('kode', 30)->unique();
            $table->string('nama_objektif');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('security_controls', function (Blueprint $table) {
            $table->id('security_control_id');
            $table->foreignId('control_objective_id')->constrained('control_objectives', 'control_objective_id')->restrictOnDelete();
            $table->string('kode', 30)->unique();
            $table->string('nama_kontrol');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('regulations', function (Blueprint $table) {
            $table->id('regulation_id');
            $table->string('kode', 30)->unique();
            $table->string('nama_regulasi');
            $table->timestamps();
        });

        Schema::create('soa_entries', function (Blueprint $table) {
            $table->id('soa_entry_id');
            $table->foreignId('security_control_id')->unique()->constrained('security_controls', 'security_control_id')->restrictOnDelete();
            $table->enum('implementasi', ['diimplementasikan', 'sebagian', 'belum', 'tidak_berlaku'])->default('belum');
            $table->text('justifikasi')->nullable();
            $table->timestamps();
        });

        Schema::create('soa_benchmarks', function (Blueprint $table) {
            $table->id('soa_benchmark_id');
            $table->foreignId('soa_entry_id')->constrained('soa_entries', 'soa_entry_id')->cascadeOnDelete();
            $table->foreignId('regulation_id')->constrained('regulations', 'regulation_id')->restrictOnDelete();
            $table->string('rujukan');
            $table->timestamps();

            $table->unique(['soa_entry_id', 'regulation_id', 'rujukan'], 'soa_benchmarks_unique');
        });

        // ---- SOP (dikelola Admin Satker; satker_id NULL = SOP global dari Super Admin) ----
        Schema::create('sops', function (Blueprint $table) {
            $table->id('sop_id');
            $table->foreignId('satker_id')->nullable()->constrained('satker', 'satker_id')->restrictOnDelete();
            $table->string('kode_sop', 50)->unique();
            $table->string('nama_sop');
            $table->text('deskripsi')->nullable();
            $table->string('versi', 20)->nullable();
            $table->string('dokumen_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_plans', function (Blueprint $table) {
            $table->id('work_plan_id');
            $table->string('kode_rencana_kerja', 30)->unique();
            $table->text('rencana_kerja');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'work_plans', 'sops', 'soa_benchmarks', 'soa_entries', 'regulations',
            'security_controls', 'control_objectives', 'control_domains',
            'vulnerabilities', 'vulnerability_types',
            'threat_source_relations', 'threat_sources', 'threats', 'threat_types',
        ] as $name) {
            Schema::dropIfExists($name);
        }
        Schema::enableForeignKeyConstraints();
    }
};
