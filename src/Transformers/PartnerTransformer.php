<?php

namespace App\Transformers;

use App\Entity\Partner;
use App\Entity\StorageLocation;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class PartnerTransformer extends StorageLocationTransformer
{
    public function getAvailableIncludes(): array
    {
        $availableIncludes = parent::getAvailableIncludes();
        $availableIncludes[] = 'users';
        $availableIncludes[] = 'profile';

        return $availableIncludes;
    }

    public function getDefaultIncludes(): array
    {
        $defaultIncludes = parent::getDefaultIncludes();
        $defaultIncludes[] = 'fulfillmentPeriod';
        $defaultIncludes[] = 'distributionMethod';

        return $defaultIncludes;
    }


    /**
     * @param Partner $partner
     * @return array
     */
    public function transform(StorageLocation $partner): array
    {
        return [
            'id' => (int) $partner->getId(),
            'title' => $partner->getTitle(),
            'status' => $partner->getStatus(),
            'partnerType' => $partner->getPartnerType(),
            'legacyId' => $partner->getLegacyId(),
            'forecastAverageMonths' => $partner->getForecastAverageMonths(),
            'canPlaceOrders' => $partner->canPlaceOrders(),
            'createdAt' => $partner->getCreatedAt()->format('c'),
            'updatedAt' => $partner->getUpdatedAt()->format('c'),
        ];
    }

    public function includeFulfillmentPeriod(Partner $partner): Item
    {
        return $this->item($partner->getFulfillmentPeriod(), new ListOptionTransformer());
    }

    public function includeDistributionMethod(Partner $partner): Item
    {
        return $this->item($partner->getDistributionMethod(), new ListOptionTransformer());
    }

    public function includeProfile(Partner $partner): Item
    {
        return $this->item($partner->getProfile(), new PartnerProfileTransformer());
    }

    public function includeUsers(Partner $partner): Collection
    {
        return $this->collection($partner->getUsers(), new PartnerUserTransformer());
    }

    public function includeContacts(StorageLocation $storageLocation): Collection
    {
        $contacts = $storageLocation->getContacts();

        return $this->collection($contacts, new PartnerContactTransformer());
    }
}
