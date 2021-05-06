<?php

namespace App\Transformers;

use App\Entity\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'groups',
        'partners',
        'activePartner'
    ];

    public function transform(User $user)
    {
        return [
            'id' => (int) $user->getId(),
            'name' => [
                'firstName' => $user->getName()->getFirstname(),
                'lastName' => $user->getName()->getLastname(),
            ],
            'email' => $user->getEmail(),
            "isAdmin" => $user->isAdmin(),
            "isPartner" => $user->isPartner(),
            'updatedAt' => $user->getUpdatedAt()->format('c'),
        ];
    }

    public function includeGroups(User $user)
    {
        return $this->collection($user->getGroups(), new GroupTransformer());
    }

    public function includePartners(User $user)
    {
        return $this->collection($user->getPartners(), new PartnerTransformer());
    }

    public function includeActivePartner(User $user)
    {
        if (!$user->getActivePartner()) {
            return null;
        }

        return $this->item($user->getActivePartner(), new PartnerTransformer());
    }
}
