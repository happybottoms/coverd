<?php

namespace App\Controller;

use App\Entity\InventoryTransaction;
use App\Entity\Partner;
use App\Entity\StorageLocation;
use App\Entity\User;
use App\Entity\Warehouse;
use App\Transformers\StorageLocationOptionTransformer;
use App\Transformers\StorageLocationTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/storage-locations")
 *
 * Class StorageLocationController
 * @package App\Controller
 */
class StorageLocationController extends BaseController
{
    protected $defaultEntityName = StorageLocation::class;

    /**
     * Get a list of Sub-classed storage locations
     *
     * @Route(path="", methods={"GET"})
     * @IsGranted({"ROLE_ADMIN", "ROLE_PARTNER_MANAGE_OWN", "ROLE_PARTNER_VIEW_ALL"})
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->hasRole(Partner::ROLE_VIEW_ALL)) {
            $storageLocations = $this->getRepository()->findAll();
        } else {
            $storageLocations = $this->getEm()->getRepository(Warehouse::class)->findAll();
            $storageLocations[] = $user->getActivePartner();
        }

        return $this->serialize($request, $storageLocations);
    }

    /**
     * Get a single StorageLocation
     *
     * @Route(path="/{id}", methods={"GET"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $storageLocation = $this->getStorageLocation($id);

        return $this->serialize($request, $storageLocation);
    }

    /**
     * Save a new storageLocation
     *
     * @Route(path="/", methods={"POST"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        throw new \Exception('Not implemented for generic storage location');
    }

    /**
     * Whole or partial update of a storageLocation
     *
     * @Route(path="/{id}", methods={"PATCH"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $params = $this->getParams($request);
        /** @var StorageLocation $storageLocation */
        $storageLocation = $this->getStorageLocation($id);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($storageLocation);

        $storageLocation->applyChangesFromArray($params);

        $this->getEm()->persist($storageLocation);
        $this->getEm()->flush();

        return $this->serialize($request, $storageLocation);
    }

    /**
     * Delete a storageLocation
     *
     * @Route(path="/{id}", methods={"DELETE"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $storageLocation = $this->getStorageLocation($id);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($storageLocation);

        $this->getEm()->remove($storageLocation);

        $this->getEm()->flush();

        return $this->success(sprintf('StorageLocation "%s" deleted.', $storageLocation->getTitle()));
    }

    public function inventoryLevels($id)
    {
        $storageLocation = $this->getStorageLocation($id);
        $levels = $this->getRepository(InventoryTransaction::class)->getStorageLocationInventory($storageLocation);

        return $levels;
    }

    protected function getStorageLocation($id): StorageLocation
    {
        /** @var StorageLocation $storageLocation */
        $storageLocation = $this->getRepository()->find($id);

        if (!$storageLocation) {
            throw new NotFoundHttpException(sprintf('Unknown StorageLocation ID: %d', $id));
        }

        return $storageLocation;
    }

    protected function getDefaultTransformer()
    {
        return new StorageLocationTransformer();
    }
}
