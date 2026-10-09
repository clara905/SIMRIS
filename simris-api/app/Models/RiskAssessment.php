<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskAssessment extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'risk_assessment_id';

    protected $fillable = [
        'users_id', 'satker_id', 'tahun', 'periode', 'kode_risiko',
        'deskripsi_risiko', 'uraian_dampak', 'kode_area',
        'nilai_kemungkinan', 'nilai_dampak', 'risk_level_id',
        'keputusan_risiko', 'keputusan_risiko_setelah', 'revisi_ke', 'status',
    ];

    /** Cakupan: satker langsung; bidang/sub-bidang lewat unit pembuat laporan. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->dataScope()) {
            'all'        => $query,
            'satker'     => $query->where('satker_id', $user->satker_id),
            'bidang'     => $query->where('satker_id', $user->satker_id)
                ->whereHas('pembuat', fn ($q) => $q->where('bidang_id', $user->bidang_id)),
            'sub_bidang' => $query->where('satker_id', $user->satker_id)
                ->whereHas('pembuat', fn ($q) => $q->where('sub_bidang_id', $user->sub_bidang_id)),
            default      => $query->whereRaw('1 = 0'),
        };
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id', 'users_id')->withTrashed();
    }

    public function riskLevel(): BelongsTo
    {
        return $this->belongsTo(RiskLevel::class, 'risk_level_id', 'risk_level_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(RiskTreatment::class, 'risk_assessment_id', 'risk_assessment_id');
    }
}