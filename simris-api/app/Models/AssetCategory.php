<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $primaryKey = 'asset_category_id';
    protected $fillable = ['parent_id', 'kode_kategori', 'nama_kategori'];
}