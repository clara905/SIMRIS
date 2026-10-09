<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing(['satker', 'bidang', 'subBidang']);

        return [
            'users_id'    => $this->users_id,
            'name'        => $this->name,
            'username'    => $this->username,
            'email'       => $this->email,
            'roles'       => $this->getRoleNames()->values(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
            'satker'      => $this->satker
                ? ['id' => $this->satker->satker_id, 'nama' => $this->satker->nama_satker] : null,
            'bidang'      => $this->bidang
                ? ['id' => $this->bidang->bidang_id, 'nama' => $this->bidang->nama_bidang] : null,
            'sub_bidang'  => $this->subBidang
                ? ['id' => $this->subBidang->sub_bidang_id, 'nama' => $this->subBidang->nama_sub_bidang] : null,
        ];
    }
}