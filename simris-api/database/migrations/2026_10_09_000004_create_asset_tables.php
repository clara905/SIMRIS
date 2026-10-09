<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id('asset_category_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('kode_kategori', 30)->unique();
            $table->string('nama_kategori');
            $table->timestamps();

            $table->foreign('parent_id')->references('asset_category_id')->on('asset_categories')->restrictOnDelete();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id('asset_id');
            $table->foreignId('asset_category_id')->constrained('asset_categories', 'asset_category_id')->restrictOnDelete();
            $table->foreignId('satker_id')->constrained('satker', 'satker_id')->restrictOnDelete();
            $table->foreignId('bidang_id')->nullable()->constrained('bidang', 'bidang_id')->restrictOnDelete();
            $table->foreignId('sub_bidang_id')->nullable()->constrained('sub_bidang', 'sub_bidang_id')->restrictOnDelete();
            $table->string('kode_aset', 50);
            $table->string('nama_aset');
            $table->text('deskripsi')->nullable();
            $table->decimal('nilai_kekritisan', 5, 2)->nullable();
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('kondisi', 50)->nullable();
            $table->string('merk')->nullable();
            $table->string('snumber')->nullable();
            $table->date('pengadaan_lama')->nullable();
            $table->date('pengadaan')->nullable();
            $table->string('lokasi')->nullable();
            $table->foreignId('created_by')->constrained('users', 'users_id')->restrictOnDelete();
            $table->string('status', 30)->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['satker_id', 'kode_aset']);
            $table->index(['satker_id', 'bidang_id', 'sub_bidang_id']);
        });

        Schema::create('asset_owners', function (Blueprint $table) {
            $table->id('asset_owner_id');
            $table->foreignId('asset_id')->constrained('assets', 'asset_id')->cascadeOnDelete();
            $table->foreignId('bidang_id')->constrained('bidang', 'bidang_id')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['asset_id', 'bidang_id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('asset_owners');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
        Schema::enableForeignKeyConstraints();
    }
};
