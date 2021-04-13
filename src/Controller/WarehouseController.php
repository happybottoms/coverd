<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Repository\WarehouseRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WarehouseController
 * @package App\Controller
 *
 * @Route(path="/api/warehouses")
 */
class WarehouseController extends StorageLocationController
{
    protected $defaultEntityName = Warehouse::class;

    /**
     *
     * @Route("/", methods={"GET"})
     * @IsGranted({"ROLE_WAREHOUSE_VIEW"})
     *
     */
    public function index(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Warehouse::ROLE_VIEW);

        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;
        $page = $request->get('page', 1);
        $limit = $request->get('per_page', 10);
        $params = $this->buildFilterParams($request);

        /** @var WarehouseRepository $repo */
        $repo = $this->getRepository(Warehouse::class);
        $partners = $repo->findAllPaged(
            $page,
            $limit,
            $sort ? $sort[0] : null,
            $sort ? $sort[1] : null,
            $params
        );

        return $this->serialize($request, $partners);
    }

    /**
     *
     * @Route("/{id<\d+>}", methods={"GET"})
     * @IsGranted({"ROLE_WAREHOUSE_VIEW"})
     *
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Warehouse::ROLE_VIEW);

        $partner = $this->getWarehouseById($id);

        return $this->serialize($request, $partner);
    }

    protected function getWarehouseById(int $id): Warehouse
    {
        /** @var ?Warehouse $warehouse */
        $warehouse = $this->getRepository()->find($id);

        if (!$warehouse) {
            throw new NotFoundHttpException(sprintf('Unknown Warehouse ID: %d', $id));
        }

        return $warehouse;
    }

    /**
     * @param Request $request
     * @return ParameterBag
     */
    protected function buildFilterParams(Request $request)
    {
        $params = new ParameterBag();

        if ($request->get('status')) {
            $params->set('status', $request->get('status'));
        }

        if ($request->get('id')) {
            $params->set('id', $request->get('id'));
        }

        return $params;
    }
}
