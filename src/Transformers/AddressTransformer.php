<?php

namespace App\Transformers;

use App\Entity\Address;
use League\Fractal\TransformerAbstract;

class AddressTransformer extends TransformerAbstract
{

    public function transform(Address $address): array
    {
        return [
            'id' => (int) $address->getId(),
            'street1' => $address->getStreet1(),
            'street2' => $address->getStreet2(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'country' => $address->getCountry(),
            'postalCode' => $address->getPostalCode(),
        ];
    }
}
