<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function create(User $user): bool
    {
        return $user->can('asset.manage');
    }

    public function update(User $user, Asset $asset): bool
    {
        if (! $user->can('asset.manage')) {
            return false;
        }

        return $user->hasRole('super_admin')
            || ($user->sub_bidang_id !== null
                && (int) $user->sub_bidang_id === (int) $asset->sub_bidang_id);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset);
    }
}