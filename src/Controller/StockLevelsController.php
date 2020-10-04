<?php

namespace App\Controller;

use App\Entity\InventoryTransaction;
use App\Entity\Product;
use App\Entity\ProductRepository;
use App\Entity\StorageLocation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/stock-levels")
 */
class StockLevelsController extends BaseController
{
    /**
     * Get a list of Sub-classed storage locations
     *
     * @Route(path="", methods={"GET"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $input = $this->getParams($request);

        $params = new ParameterBag([
            'endingAt' =>  $request->get('endingAt'),
        ]);

        if ($request->get('location')) {
            $params->set('location', $this->getRepository(StorageLocation::class)->find($request->get('location')));
        }

        if ($request->get('locationType')) {
            $params->set('locationType', $request->get('locationType'));
        }

        /** @var ProductRepository $productRepository */
        $productRepository = $this->getRepository(Product::class);

        /** @var Product[] $products */
        $products = $productRepository->findAllSorted();

        $availableLevels = $this->getRepository(InventoryTransaction::class)->getStockLevels(true, $params);
        $levels = $this->getRepository(InventoryTransaction::class)->getStockLevels(false, $params);

        // Build a list based on the product list
        $stockLevels = array_map(function (Product $product) use ($availableLevels, $levels) {
            // Get the current level for the product
            $level = array_filter($levels, function ($item) use ($product) {
                return $item['id'] == $product->getId();
            });
            $level = reset($level);

            // Get the available level for the product
            $available = array_filter($availableLevels, function ($item) use ($product) {
                return $item['id'] == $product->getId();
            });
            $available = reset($available);

            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'category' => $product->getProductCategory()->getName(),
                'balance' => (int) $level['balance'] ?: 0,
                'availableBalance' => (int) $available['balance'] ?: 0
            ];
        }, $products);

        return new JsonResponse($stockLevels);
    }

    protected function getDefaultTransformer()
    {
//        return new StorageLocationTransformer;
    }
}
