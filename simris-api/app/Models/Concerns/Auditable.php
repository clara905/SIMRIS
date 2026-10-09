<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(fn ($m) => AuditLog::record('create', $m->getTable(), $m->getKey(), null, $m->getAttributes()));

        static::updated(fn ($m) => AuditLog::record(
            'update',
            $m->getTable(),
            $m->getKey(),
            array_intersect_key($m->getRawOriginal(), $m->getChanges()),
            $m->getChanges(),
        ));

        static::deleted(fn ($m) => AuditLog::record('delete', $m->getTable(), $m->getKey(), $m->getAttributes()));
    }
}