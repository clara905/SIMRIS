<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null; // tabel hanya punya created_at

    protected $primaryKey = 'audit_log_id';
    protected $fillable = [
        'users_id', 'table_name', 'record_id', 'action',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    public static function record(
        string $action,
        string $table,
        string|int $recordId,
        ?array $old = null,
        ?array $new = null,
    ): void {
        static::create([
            'users_id'   => auth()->id(),
            'table_name' => $table,
            'record_id'  => (string) $recordId,
            'action'     => $action,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }
}