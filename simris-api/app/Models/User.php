<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, SoftDeletes;

    public const ROLE_PRIORITY = ['super_admin', 'admin_satker', 'kasatker', 'kabid', 'kasubbid', 'staf'];

    protected $primaryKey = 'users_id';
    protected string $guard_name = 'web';

    protected $fillable = [
        'satker_id', 'bidang_id', 'sub_bidang_id',
        'name', 'username', 'email', 'password', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean'];
    }

    /** Rantai unit selalu konsisten: sub_bidang -> bidang -> satker diturunkan otomatis. */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->sub_bidang_id) {
                $sub = SubBidang::with('bidang')->find($user->sub_bidang_id);
                $user->bidang_id = $sub?->bidang_id;
                $user->satker_id = $sub?->bidang?->satker_id;
            } elseif ($user->bidang_id) {
                $user->satker_id = Bidang::find($user->bidang_id)?->satker_id;
            }
        });
    }

    public function primaryRole(): ?string
    {
        foreach (self::ROLE_PRIORITY as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }
        return null;
    }

    /**
     * Cakupan data user: 'all' | 'satker' | 'bidang' | 'sub_bidang' | null.
     * null = akun belum ditempatkan di unit yang sesuai role-nya -> TIDAK BOLEH melihat data apa pun.
     */
    public function dataScope(): ?string
    {
        return match ($this->primaryRole()) {
            'super_admin' => 'all',
            'admin_satker', 'kasatker' => $this->satker_id ? 'satker' : null,
            'kabid' => $this->bidang_id ? 'bidang' : null,
            'kasubbid', 'staf' => $this->sub_bidang_id ? 'sub_bidang' : null,
            default => null,
        };
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class, 'satker_id', 'satker_id');
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id', 'bidang_id');
    }

    public function subBidang(): BelongsTo
    {
        return $this->belongsTo(SubBidang::class, 'sub_bidang_id', 'sub_bidang_id');
    }
}