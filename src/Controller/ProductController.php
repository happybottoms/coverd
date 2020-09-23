<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Transformers\ProductTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ProductController
 * @package App\Http\Controllers
 *
 * @Route(path="/api/products")
 */
class ProductController extends BaseController
{
    protected $defaultEntityName = Product::class;

    /**
     * Get a list of Products
     *
     * @Route(path="", methods={"GET"})
     * @IsGranted({"ROLE_USER"})
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $partnerOrderable = $request->get('partnerOrderable');

        if ($partnerOrderable == null) {
            $products = $this->getRepository()->findAllSorted();
        } else {
            $products = $this->getRepository()->findByPartnerOrderable($partnerOrderable);
        }

        return $this->serialize($request, $products);
    }

    /**
     * Get a single Product
     *
     * @Route(path="/{id<\d+>}", methods={"GET"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $product = $this->getProduct($id);

        return $this->serialize($request, $product);
    }

    /**
     * Save a new product
     *
     * @Route(path="", methods={"POST"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request, ValidatorInterface $validator)
    {
        $params = $this->getParams($request);

        $product = new Product($request->get('name'));

        $product->applyChangesFromArray($params);

        if ($params['productCategory']['id']) {
            $newCategory = $this->getEm()->find(ProductCategory::class, $params['productCategory']['id']);
            $product->setProductCategory($newCategory);
        }

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($product);

        $this->validate($product, $validator);

        $this->getEm()->persist($product);
        $this->getEm()->flush();

        return $this->serialize($request, $product);
    }

    /**
     * Saves the order index of the products
     *
     * @Route(path="/order", methods={"POST"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     */
    public function storeOrder(Request $request): JsonResponse
    {
        $params = $this->getParams($request);
        $ids = $params['ids'];

        /** @var Product[] $products */
        $products = $this->getRepository()->findAllSorted();

        foreach ($products as $product) {
            $product->setOrderIndex(array_search($product->getId(), $ids));
        }

        $this->getEm()->flush();

        return $this->serialize($request, $products);
    }

    /**
     * Whole or partial update of a product
     *
     * @Route(path="/{id<\d+>}", methods={"PATCH"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @param $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        $params = $this->getParams($request);
        /** @var Product $product */
        $product = $this->getProduct($id);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($product);

        $product->applyChangesFromArray($params);

        if ($params['productCategory']['id'] != $product->getProductCategory()->getId()) {
            $newCategory = $this->getEm()->find(ProductCategory::class, $params['productCategory']['id']);
            $product->setProductCategory($newCategory);
        }

        $this->getEm()->persist($product);
        $this->getEm()->flush();

        return $this->serialize($request, $product);
    }

    /**
     * Delete a product
     *
     * @Route(path="/{id<\d+>}", methods={"DELETE"})
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $product = $this->getProduct($id);

        // TODO: get permissions working (#1)
        // $this->checkEditPermissions($product);

        $this->getEm()->remove($product);

        $this->getEm()->flush();

        return $this->success(sprintf('Product "%s" deleted.', $product->getTitle()));
    }

    /**
     * Get current inventory stats for the give product
     *
     * @Route(path="/{id<\d+>}/inventory")
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param $id
     */
    public function inventory($id)
    {
        $product = $this->getProduct($id);

        $inventory = [
            'warehouse' => $this->getRepository()->getWarehouseInventory($product),
            'partner' => $this->getRepository()->getPartnerInventory($product),
            'total' => $this->getRepository()->getNetworkInventory($product),
        ];

        return new JsonResponse($inventory);
    }

    /**
     * @param $id
     * @return null|Product
     * @throws NotFoundApiException
     */
    protected function getProduct($id)
    {
        /** @var Product $product */
        $product = $this->getRepository()->find($id);

        if (!$product) {
            throw new NotFoundHttpException(sprintf('Unknown Product ID: %d', $id));
        }

        return $product;
    }

    protected function getDefaultTransformer()
    {
        return new ProductTransformer();
    }
}
