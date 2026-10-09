<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Satker extends Model
{
    use SoftDeletes;

    protected $table = 'satker';
    protected $primaryKey = 'satker_id';
    protected $fillable = ['kode_satker', 'nama_satker', 'kepala_user_id'];

    public function bidang(): HasMany
    {
        return $this->hasMany(Bidang::class, 'satker_id', 'satker_id');
    }

    public function kepala(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_user_id', 'users_id');
    }
}