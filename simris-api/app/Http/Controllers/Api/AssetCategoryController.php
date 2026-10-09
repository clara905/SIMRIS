<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\JsonResponse;

class AssetCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => AssetCategory::orderBy('nama_kategori')->get(['asset_category_id', 'nama_kategori']),
        ]);
    }
}