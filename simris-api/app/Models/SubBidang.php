<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubBidang extends Model
{
    use SoftDeletes;

    protected $table = 'sub_bidang';
    protected $primaryKey = 'sub_bidang_id';
    protected $fillable = ['bidang_id', 'kode_sub_bidang', 'nama_sub_bidang', 'kepala_user_id'];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id', 'bidang_id');
    }

    public function kepala(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_user_id', 'users_id');
    }
}