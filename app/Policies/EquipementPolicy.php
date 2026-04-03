<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Equipement;
use App\Models\User;

final class EquipementPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'Gestionnaire';
    }

    public function viewAny(User $user): bool
    {
        return $user->role === 'Gestionnaire';
    }

    public function update(User $user, Equipement $equipement): bool
    {
        return $user->role === 'Gestionnaire';
    }

    public function view(User $user, Equipement $equipement): bool
    {
        return false;
    }

    public function delete(User $user, Equipement $equipement): bool
    {
        return false;
    }

    public function restore(User $user, Equipement $equipement): bool
    {
        return false;
    }

    public function forceDelete(User $user, Equipement $equipement): bool
    {
        return false;
    }
}
