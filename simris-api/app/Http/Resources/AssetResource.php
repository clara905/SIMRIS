<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'asset_id'          => $this->asset_id,
            'kode_aset'         => $this->kode_aset,
            'nama_aset'         => $this->nama_aset,
            'asset_category_id' => $this->asset_category_id,
            'kategori'          => $this->kategori?->nama_kategori ?? '-',
            'nilai_kekritisan'  => $this->nilai_kekritisan !== null ? (float) $this->nilai_kekritisan : null,
            'jumlah'            => $this->jumlah,
            'kondisi'           => $this->kondisi,
            'merk'              => $this->merk,
            'snumber'           => $this->snumber,
            'pengadaan'         => $this->pengadaan?->format('Y-m-d'),
            'lokasi'            => $this->lokasi,
            'deskripsi'         => $this->deskripsi,
            'sub_bidang'        => $this->subBidang?->nama_sub_bidang,
            'status'            => $this->status,
        ];
    }
}
