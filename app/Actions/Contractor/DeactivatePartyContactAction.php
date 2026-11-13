<?php

declare(strict_types=1);

namespace App\Actions\Contractor;

use App\Enums\Commercial\PartyContactStatus;
use App\Models\PartyContact;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class DeactivatePartyContactAction
{
    public function execute(User $actor, PartyContact $contact): PartyContact
    {
        if ($contact->contractor_id !== null) {
            Gate::forUser($actor)->authorize('update', $contact->contractor);
        } elseif ($contact->partner_id !== null) {
            Gate::forUser($actor)->authorize('update', $contact->partner);
        } else {
            throw new InvalidArgumentException(__('Contact has no owner.'));
        }

        $contact->forceFill([
            'status' => PartyContactStatus::Inactive,
        ])->save();

        activity('PartyContact')
            ->performedOn($contact)
            ->causedBy($actor)
            ->withProperties(['event' => 'deactivated'])
            ->log('Party contact deactivated');

        return $contact->refresh();
    }
}
