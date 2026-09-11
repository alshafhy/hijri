<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('offer.view');
    }

    public function view(User $user, Offer $offer): bool
    {
        return $user->can('offer.view');
    }

    public function create(User $user): bool
    {
        return $user->can('offer.create');
    }

    public function update(User $user, Offer $offer): bool
    {
        return $user->can('offer.edit');
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $user->can('offer.delete');
    }

    public function activate(User $user, Offer $offer): bool
    {
        return $user->can('offer.activate');
    }

    public function deactivate(User $user, Offer $offer): bool
    {
        return $user->can('offer.activate');
    }
}
