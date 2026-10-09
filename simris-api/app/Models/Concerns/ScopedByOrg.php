<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopedByOrg
{
    /** Batasi query ke data yang boleh dilihat user. Default AMAN: tidak ada data. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->dataScope()) {
            'all'        => $query,
            'satker'     => $query->where($this->qualifyColumn('satker_id'), $user->satker_id),
            'bidang'     => $query->where($this->qualifyColumn('bidang_id'), $user->bidang_id),
            'sub_bidang' => $query->where($this->qualifyColumn('sub_bidang_id'), $user->sub_bidang_id),
            default      => $query->whereRaw('1 = 0'),
        };
    }
}