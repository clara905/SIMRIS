<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskTreatment extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'risk_treatments_id';

    protected $fillable = [
        'risk_assessment_id', 'work_plan_id', 'risk_assessment_security_controls_id',
        'rencana_mitigasi', 'penanggung_jawab', 'target_date', 'status', 'catatan',
    ];

    protected function casts(): array
    {
        return ['target_date' => 'date'];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class, 'risk_assessment_id', 'risk_assessment_id');
    }
}