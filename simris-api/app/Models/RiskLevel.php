<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskLevel extends Model
{
    protected $primaryKey = 'risk_level_id';
    protected $fillable = ['nama_level', 'skor_min', 'skor_max', 'warna'];
}