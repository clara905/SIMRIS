<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bidang extends Model
{
    use SoftDeletes;

    protected $table = 'bidang';
    protected $primaryKey = 'bidang_id';
    protected $fillable = ['satker_id', 'kode_bidang', 'nama_bidang', 'kepala_user_id'];

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class, 'satker_id', 'satker_id');
    }

    public function subBidang(): HasMany
    {
        return $this->hasMany(SubBidang::class, 'bidang_id', 'bidang_id');
    }

    public function kepala(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_user_id', 'users_id');
    }
}