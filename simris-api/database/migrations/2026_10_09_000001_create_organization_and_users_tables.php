<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hierarki: satker -> bidang -> sub_bidang -> users.
 * Hapus migration bawaan 0001_01_01_000000_create_users_table.php (diganti file ini).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satker', function (Blueprint $table) {
            $table->id('satker_id');
            $table->unsignedBigInteger('kepala_user_id')->nullable(); // Kasatker (FK ditambah setelah users ada)
            $table->string('kode_satker', 30)->unique();
            $table->string('nama_satker');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bidang', function (Blueprint $table) {
            $table->id('bidang_id');
            $table->foreignId('satker_id')->constrained('satker', 'satker_id')->restrictOnDelete();
            $table->unsignedBigInteger('kepala_user_id')->nullable(); // Kabid/Kabag
            $table->string('kode_bidang', 30);
            $table->string('nama_bidang');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['satker_id', 'kode_bidang']);
        });

        Schema::create('sub_bidang', function (Blueprint $table) {
            $table->id('sub_bidang_id');
            $table->foreignId('bidang_id')->constrained('bidang', 'bidang_id')->restrictOnDelete();
            $table->unsignedBigInteger('kepala_user_id')->nullable(); // Kasubbid
            $table->string('kode_sub_bidang', 30);
            $table->string('nama_sub_bidang');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['bidang_id', 'kode_sub_bidang']);
        });

        // Role disimpan di tabel Spatie (roles, model_has_roles), bukan kolom enum.
        // Super Admin: satker_id/bidang_id/sub_bidang_id = NULL (lintas satker).
        Schema::create('users', function (Blueprint $table) {
            $table->id('users_id');
            $table->foreignId('satker_id')->nullable()->constrained('satker', 'satker_id')->restrictOnDelete();
            $table->foreignId('bidang_id')->nullable()->constrained('bidang', 'bidang_id')->restrictOnDelete();
            $table->foreignId('sub_bidang_id')->nullable()->constrained('sub_bidang', 'sub_bidang_id')->restrictOnDelete();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // FK "kepala" ditambahkan setelah users ada (menghindari circular dependency)
        foreach (['satker', 'bidang', 'sub_bidang'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreign('kepala_user_id')->references('users_id')->on('users')->nullOnDelete();
            });
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sub_bidang');
        Schema::dropIfExists('bidang');
        Schema::dropIfExists('satker');
        Schema::enableForeignKeyConstraints();
    }
};
