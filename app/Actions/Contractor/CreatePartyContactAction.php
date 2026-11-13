<?php

declare(strict_types=1);

namespace App\Actions\Contractor;

use App\Enums\Commercial\PartyContactOwnerType;
use App\Enums\Commercial\PartyContactStatus;
use App\Models\Contractor;
use App\Models\Partner;
use App\Models\PartyContact;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class CreatePartyContactAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, Partner|Contractor $owner, array $data): PartyContact
    {
        Gate::forUser($actor)->authorize('update', $owner);

        if ($owner instanceof Partner) {
            $payload = [
                'partner_id' => $owner->id,
                'contractor_id' => null,
                'owner_type' => PartyContactOwnerType::Partner,
            ];
        } elseif ($owner instanceof Contractor) {
            $payload = [
                'partner_id' => null,
                'contractor_id' => $owner->id,
                'owner_type' => PartyContactOwnerType::Contractor,
            ];
        } else {
            throw new InvalidArgumentException(__('Contact has no owner.'));
        }

        $contact = PartyContact::query()->create(array_merge($payload, [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'status' => PartyContactStatus::Active,
        ]));

        activity('PartyContact')
            ->performedOn($contact)
            ->causedBy($actor)
            ->withProperties(['action' => 'created'])
            ->log('Party contact created');

        return $contact->refresh();
    }
}
