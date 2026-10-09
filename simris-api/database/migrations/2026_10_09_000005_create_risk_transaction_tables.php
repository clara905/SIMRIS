<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transaksi risiko: penilaian, approval berjenjang, treatment, hasil mitigasi,
 * bukti mitigasi (fleksibel: deskripsi + file opsional), dan audit log.
 *
 * Catatan: nilai_risiko & nilai_risiko_sisa adalah STORED GENERATED COLUMN (L x D).
 * risk_level_id tetap diisi backend dari lookup risk_levels (skor_min..skor_max).
 * FK pada kolom nilai_* sengaja RESTRICT (MySQL melarang CASCADE/SET NULL di kolom generated).
 */
return new class extends Migration
{
    private array $keputusan = ['diterima', 'dimitigasi', 'dihindari', 'dialihkan'];

    public function up(): void
    {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id('risk_assessment_id');
            $table->foreignId('users_id')->constrained('users', 'users_id')->restrictOnDelete();
            $table->foreignId('satker_id')->constrained('satker', 'satker_id')->restrictOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->string('periode', 30)->nullable();
            $table->string('kode_risiko', 40);
            $table->text('deskripsi_risiko');
            $table->text('uraian_dampak')->nullable();
            $table->string('kode_area', 20);
            $table->unsignedTinyInteger('nilai_kemungkinan');
            $table->unsignedTinyInteger('nilai_dampak');
            $table->unsignedSmallInteger('nilai_risiko')->storedAs('nilai_kemungkinan * nilai_dampak');
            $table->foreignId('risk_level_id')->constrained('risk_levels', 'risk_level_id')->restrictOnDelete();
            $table->enum('keputusan_risiko', $this->keputusan)->nullable();
            $table->enum('keputusan_risiko_setelah', $this->keputusan)->nullable();
            $table->unsignedSmallInteger('revisi_ke')->default(0);
            $table->enum('status', [
                'draft', 'diajukan', 'review_kasubbid', 'review_kabid', 'review_kasatker',
                'disetujui', 'ditolak', 'revisi',
            ])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kode_area')->references('kode_area')->on('area_dampak')->restrictOnDelete();
            $table->foreign('nilai_kemungkinan')->references('level')->on('likelihood_levels')->restrictOnDelete();
            $table->foreign('nilai_dampak')->references('level')->on('impact_levels')->restrictOnDelete();

            $table->unique(['satker_id', 'tahun', 'kode_risiko']);
            $table->index(['satker_id', 'status']);
        });

        Schema::create('risk_approvals', function (Blueprint $table) {
            $table->id('risk_approvals_id');
            $table->foreignId('risk_assessment_id')->constrained('risk_assessments', 'risk_assessment_id')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users', 'users_id')->restrictOnDelete();
            $table->enum('approval_level', ['kasubbid', 'kabid', 'kasatker']);
            $table->unsignedSmallInteger('revisi_ke')->default(0);
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'revisi'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['risk_assessment_id', 'approval_level', 'revisi_ke'], 'risk_approvals_unique');
        });

        // ---- Relasi penilaian risiko ----
        Schema::create('risk_assessment_assets', function (Blueprint $table) {
            $table->id('risk_assessment_assets_id');
            $table->foreignId('risk_assessment_id')->constrained('risk_assessments', 'risk_assessment_id')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets', 'asset_id')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['risk_assessment_id', 'asset_id']);
        });

        Schema::create('risk_assessment_threats', function (Blueprint $table) {
            $table->id('risk_assessment_threats_id');
            $table->foreignId('risk_assessment_id')->constrained('risk_assessments', 'risk_assessment_id')->cascadeOnDelete();
            $table->string('kode_ancaman', 20);
            $table->timestamps();

            $table->foreign('kode_ancaman')->references('kode_ancaman')->on('threats')->restrictOnDelete();
            $table->unique(['risk_assessment_id', 'kode_ancaman']);
        });

        Schema::create('risk_assessment_vulnerabilities', function (Blueprint $table) {
            $table->id('risk_assessment_vulnerabilities_id');
            $table->foreignId('risk_assessment_id')->constrained('risk_assessments', 'risk_assessment_id')->cascadeOnDelete();
            $table->string('kode_kerentanan', 20);
            $table->text('uraian_kerentanan')->nullable();
            $table->timestamps();

            $table->foreign('kode_kerentanan')->references('kode_kerentanan')->on('vulnerabilities')->restrictOnDelete();
            $table->unique(['risk_assessment_id', 'kode_kerentanan'], 'ra_vuln_unique');
        });

        Schema::create('risk_assessment_security_controls', function (Blueprint $table) {
            $table->id('risk_assessment_security_controls_id');
            $table->foreignId('risk_assessment_id')->constrained('risk_assessments', 'risk_assessment_id')->cascadeOnDelete();
            $table->foreignId('security_control_id')->constrained('security_controls', 'security_control_id')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['risk_assessment_id', 'security_control_id'], 'ra_controls_unique');
        });

        Schema::create('risk_assessment_sops', function (Blueprint $table) {
            $table->id('risk_assessment_sops_id');
            $table->foreignId('risk_assessment_id')->constrained('risk_assessments', 'risk_assessment_id')->cascadeOnDelete();
            $table->foreignId('sop_id')->constrained('sops', 'sop_id')->restrictOnDelete();
            $table->text('kontrol')->nullable();
            $table->timestamps();

            $table->unique(['risk_assessment_id', 'sop_id']);
        });

        // ---- Treatment & mitigasi ----
        Schema::create('risk_treatments', function (Blueprint $table) {
            $table->id('risk_treatments_id');
            $table->foreignId('risk_assessment_id')->constrained('risk_assessments', 'risk_assessment_id')->cascadeOnDelete();
            $table->foreignId('work_plan_id')->constrained('work_plans', 'work_plan_id')->restrictOnDelete();
            $table->unsignedBigInteger('risk_assessment_security_controls_id')->nullable();
            $table->text('rencana_mitigasi');
            $table->unsignedBigInteger('penanggung_jawab');
            $table->date('target_date')->nullable();
            $table->enum('status', ['direncanakan', 'berjalan', 'selesai', 'ditunda'])->default('direncanakan');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('risk_assessment_security_controls_id', 'rt_ra_controls_fk')
                ->references('risk_assessment_security_controls_id')
                ->on('risk_assessment_security_controls')->nullOnDelete();
            $table->foreign('penanggung_jawab')->references('users_id')->on('users')->restrictOnDelete();
        });

        Schema::create('risk_mitigation_results', function (Blueprint $table) {
            $table->id('risk_mitigation_results_id');
            $table->unsignedBigInteger('risk_assessment_security_controls_id');
            $table->unsignedBigInteger('risk_treatments_id')->nullable();
            $table->unsignedBigInteger('users_id');
            $table->date('tanggal_selesai')->nullable();
            $table->text('hasil_mitigasi')->nullable();
            $table->text('kondisi_setelah_mitigasi')->nullable();
            $table->unsignedTinyInteger('nilai_kemungkinan_sisa')->nullable();
            $table->unsignedTinyInteger('nilai_dampak_sisa')->nullable();
            $table->unsignedSmallInteger('nilai_risiko_sisa')->nullable()
                ->storedAs('nilai_kemungkinan_sisa * nilai_dampak_sisa');
            $table->unsignedBigInteger('risk_level_sisa_id')->nullable();
            $table->enum('status', ['diajukan', 'diverifikasi', 'ditolak'])->default('diajukan');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('risk_assessment_security_controls_id', 'rmr_ra_controls_fk')
                ->references('risk_assessment_security_controls_id')
                ->on('risk_assessment_security_controls')->restrictOnDelete();
            $table->foreign('risk_treatments_id')->references('risk_treatments_id')->on('risk_treatments')->nullOnDelete();
            $table->foreign('users_id')->references('users_id')->on('users')->restrictOnDelete();
            $table->foreign('nilai_kemungkinan_sisa')->references('level')->on('likelihood_levels')->restrictOnDelete();
            $table->foreign('nilai_dampak_sisa')->references('level')->on('impact_levels')->restrictOnDelete();
            $table->foreign('risk_level_sisa_id')->references('risk_level_id')->on('risk_levels')->restrictOnDelete();
            $table->foreign('verified_by')->references('users_id')->on('users')->nullOnDelete();
        });

        // Bukti mitigasi fleksibel: boleh deskripsi saja, atau deskripsi + file.
        Schema::create('mitigation_evidences', function (Blueprint $table) {
            $table->id('mitigation_evidence_id');
            $table->foreignId('risk_mitigation_results_id')
                ->constrained('risk_mitigation_results', 'risk_mitigation_results_id')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->string('file_name')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->constrained('users', 'users_id')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('audit_log_id');
            $table->unsignedBigInteger('users_id')->nullable();
            $table->string('table_name', 100);
            $table->string('record_id', 64);
            $table->enum('action', ['create', 'update', 'delete', 'approve', 'reject', 'login']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('users_id')->references('users_id')->on('users')->nullOnDelete();
            $table->index(['table_name', 'record_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'audit_logs', 'mitigation_evidences', 'risk_mitigation_results', 'risk_treatments',
            'risk_assessment_sops', 'risk_assessment_security_controls',
            'risk_assessment_vulnerabilities', 'risk_assessment_threats', 'risk_assessment_assets',
            'risk_approvals', 'risk_assessments',
        ] as $name) {
            Schema::dropIfExists($name);
        }
        Schema::enableForeignKeyConstraints();
    }
};
