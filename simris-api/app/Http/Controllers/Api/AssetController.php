<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Models\SubBidang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class AssetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $assets = Asset::with(['kategori', 'subBidang'])
            ->visibleTo($request->user())
            ->orderByDesc('asset_id')
            ->get();

        return AssetResource::collection($assets);
    }

    public function store(AssetRequest $request): JsonResponse
    {
        $user = $request->user();

        // Unit aset diambil dari akun yang login, bukan dari input klien
        $sub = $user->sub_bidang_id
            ? $user->subBidang
            : SubBidang::find($request->integer('sub_bidang_id'));
        $sub->loadMissing('bidang');

        $asset = Asset::create([
            ...$request->safe()->except('sub_bidang_id'),
            'satker_id'     => $sub->bidang->satker_id,
            'bidang_id'     => $sub->bidang_id,
            'sub_bidang_id' => $sub->sub_bidang_id,
            'created_by'    => $user->users_id,
            'status'        => 'aktif',
        ]);

        return (new AssetResource($asset->load(['kategori', 'subBidang'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(AssetRequest $request, Asset $asset): AssetResource
    {
        // Unit aset tidak bisa dipindah lewat endpoint ini
        $asset->update($request->safe()->except('sub_bidang_id'));

        return new AssetResource($asset->load(['kategori', 'subBidang']));
    }

    public function destroy(Asset $asset): Response
    {
        Gate::authorize('delete', $asset);
        $asset->delete(); // soft delete

        return response()->noContent();
    }
}