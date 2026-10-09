<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\ScopedByOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use Auditable, ScopedByOrg, SoftDeletes;

    protected $primaryKey = 'asset_id';

    protected $fillable = [
        'asset_category_id', 'satker_id', 'bidang_id', 'sub_bidang_id',
        'kode_aset', 'nama_aset', 'deskripsi', 'nilai_kekritisan', 'jumlah',
        'kondisi', 'merk', 'snumber', 'pengadaan_lama', 'pengadaan',
        'lokasi', 'created_by', 'status',
    ];

    protected function casts(): array
    {
        return ['pengadaan' => 'date', 'pengadaan_lama' => 'date'];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id', 'asset_category_id');
    }

    public function subBidang(): BelongsTo
    {
        return $this->belongsTo(SubBidang::class, 'sub_bidang_id', 'sub_bidang_id');
    }
}