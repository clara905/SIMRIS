<?php

namespace App\Http\Requests;

use App\Models\Asset;
use App\Models\SubBidang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $asset = $this->route('asset');

        return $asset instanceof Asset
            ? Gate::allows('update', $asset)
            : Gate::allows('create', Asset::class);
    }

    public function rules(): array
    {
        $user = $this->user();
        $asset = $this->route('asset'); // null saat create

        $satkerId = $asset?->satker_id
            ?? $user->satker_id
            ?? SubBidang::with('bidang')->find($this->input('sub_bidang_id'))?->bidang?->satker_id;

        return [
            // Hanya dipakai saat create oleh user yang tidak punya sub-bidang (mis. Super Admin)
            'sub_bidang_id' => [
                Rule::requiredIf(fn () => $this->isMethod('POST') && ! $user->sub_bidang_id),
                'nullable', 'integer',
                Rule::exists('sub_bidang', 'sub_bidang_id')->whereNull('deleted_at'),
            ],
            'asset_category_id' => ['required', 'integer', 'exists:asset_categories,asset_category_id'],
            'kode_aset' => [
                'required', 'string', 'max:50',
                Rule::unique('assets', 'kode_aset')
                    ->where('satker_id', $satkerId)
                    ->ignore($asset?->asset_id, 'asset_id'),
            ],
            'nama_aset'        => ['required', 'string', 'max:255'],
            'nilai_kekritisan' => ['nullable', 'numeric', 'between:0,5'],
            'jumlah'           => ['required', 'integer', 'min:1'],
            'kondisi'          => ['nullable', 'string', 'max:50'],
            'merk'             => ['nullable', 'string', 'max:255'],
            'snumber'          => ['nullable', 'string', 'max:255'],
            'pengadaan'        => ['nullable', 'date'],
            'lokasi'           => ['nullable', 'string', 'max:255'],
            'deskripsi'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_aset.unique' => 'Kode aset sudah dipakai di satker ini.',
        ];
    }
}