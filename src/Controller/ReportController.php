<?php

namespace App\Controller;

use App\Entity\InventoryTransaction;
use App\Repository\InventoryTransactionRepository;
use App\Entity\Orders\BulkDistribution;
use App\Repository\Orders\BulkDistributionOrderRepository;
use App\Entity\Orders\PartnerOrder;
use App\Repository\Orders\PartnerOrderRepository;
use App\Entity\Orders\SupplyOrder;
use App\Repository\Orders\SupplyOrderRepository;
use App\Entity\Partner;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Entity\StorageLocation;
use App\Entity\Supplier;
use App\Reports\DistributionTotalsExcel;
use App\Reports\DistributionTotalsReport;
use App\Reports\PartnerInventoryExcel;
use App\Reports\PartnerInventoryReport;
use App\Reports\PartnerOrderTotalsExcel;
use App\Reports\PartnerOrderTotalsReport;
use App\Reports\SupplierTotalsExcel;
use App\Reports\SupplierTotalsReport;
use App\Reports\TransactionExcel;
use App\Transformers\Report\DistributionTotalsReportTransformer;
use App\Transformers\Report\InventoryTransactionReportTransformer;
use App\Transformers\Report\PartnerInventoryReportTransformer;
use App\Transformers\Report\PartnerOrderTotalsReportTransformer;
use App\Transformers\Report\SupplierTotalsReportTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReportController
 *
 * @Route(path="/api/reports")
 */
class ReportController extends BaseController
{
    /**
     *
     * @Route(path="/transactions")
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function transactionsReport(Request $request)
    {
        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;
        $page = $request->get('download') ? null : $request->get('page', 1);
        $limit = $request->get('download') ? null : $request->get('per_page', 10);

        $params = new ParameterBag($this->getParams($request));

        if ($request->get('location')) {
            $params->set('location', $this->getRepository(StorageLocation::class)->find($request->get('location')));
        }

        if ($request->get('product')) {
            $params->set('product', $this->getRepository(Product::class)->find($request->get('product')));
        }

        /** @var InventoryTransactionRepository $repo */
        $repo = $this->getRepository(InventoryTransaction::class);

        $transactions = $repo->findAllPaged(
            $page,
            $limit,
            $sort ? $sort[0] : null,
            $sort ? $sort[1] : null,
            $params
        );

        $total = $repo->findAllCount($params);

        if ($request->get('download')) {
            $excelReport = new TransactionExcel($transactions);

            $writer = $excelReport->buildExcel();
            // redirect output to client browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Transaction.' . date('c') . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit();
        }

        $meta = [
            'pagination' => [
                'total' => (int) $total,
                'per_page' => (int) $limit,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $limit),
                "next_page_url" => null,
                "prev_page_url" => null,
                'from' => (($page - 1) * $limit) + 1,
                'to' => ($page * $limit),
            ]
        ];

        return $this->serialize(
            $request,
            $transactions,
            new InventoryTransactionReportTransformer(),
            $meta
        );
    }

    /**
     *
     * @Route(path="/supplier-totals")
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function supplierTotalsReport(Request $request)
    {
        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;
        $page = $request->get('page', 1);
        $limit = $request->get('per_page', 10);

        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(Product::class);

        $params = new ParameterBag($this->getParams($request));

        if ($request->get('supplier')) {
            $params->set('supplier', $this->getRepository(Supplier::class)->find($request->get('supplier')));
        }

        if ($request->get('product')) {
            $params->set('product', $productRepo->find($request->get('product')));
        }

        /** @var SupplyOrderRepository $repo */
        $repo = $this->getRepository(SupplyOrder::class);

        /** @var SupplyOrder[] $orders */
        $orders = $repo->supplierTotals(
            $sort ? $sort[0] : null,
            $sort ? $sort[1] : null,
            $params
        );

        $total = $repo->findSupplierTotalsCount($params);

        $results = new SupplierTotalsReport();

        foreach ($orders as $order) {
            $row = $results->getRow($order->getSupplier());
            foreach ($order->getLineItems() as $lineItem) {
                $row->addProductTotal($lineItem->getProduct(), $lineItem->getQuantity());
            }
        }

        if ($request->get('download')) {
            $excelReport = new SupplierTotalsExcel($results, $productRepo->findByPartnerOrderable(true));

            $writer = $excelReport->buildExcel();
            // redirect output to client browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="SupplyTotals.' . date('c') . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit();
        }

        $meta = [
            'pagination' => [
                'total' => (int) $total,
                'per_page' => (int) $limit,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $limit),
                "next_page_url" => null,
                "prev_page_url" => null,
                'from' => (($page - 1) * $limit) + 1,
                'to' => ($page * $limit),
            ]
        ];

        return $this->serialize(
            $request,
            $results->getRows()->slice($page - 1 * $limit, $limit),
            new SupplierTotalsReportTransformer(),
            $meta
        );
    }

    /**
     *
     * @Route(path="/distribution-totals")
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function distributionTotalsReport(Request $request)
    {
        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;
        $page = $request->get('page', 1);
        $limit = $request->get('per_page', 10);

        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(Product::class);

        $params = new ParameterBag($this->getParams($request));

        if ($request->get('partner')) {
            $params->set('partner', $this->getRepository(Partner::class)->find($request->get('partner')));
        }

        if ($request->get('product')) {
            $params->set('product', $productRepo->find($request->get('product')));
        }

        /** @var BulkDistributionOrderRepository $repo */
        $repo = $this->getRepository(BulkDistribution::class);

        /** @var BulkDistribution[] $orders */
        $orders = $repo->distributionTotals(
            $sort ? $sort[0] : null,
            $sort ? $sort[1] : null,
            $params
        );

        $total = $repo->findDistributionTotalsCount($params);

        $results = new DistributionTotalsReport();

        foreach ($orders as $order) {
            $row = $results->getRow($order->getPartner());
            foreach ($order->getLineItems() as $lineItem) {
                $row->addProductTotal($lineItem->getProduct(), $lineItem->getQuantity());
            }
        }

        if ($request->get('download')) {
            $excelReport = new DistributionTotalsExcel($results, $productRepo->findByPartnerOrderable(true));

            $writer = $excelReport->buildExcel();
            // redirect output to client browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="DistributionTotals.' . date('c') . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit();
        }

        $meta = [
            'pagination' => [
                'total' => (int) $total,
                'per_page' => (int) $limit,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $limit),
                "next_page_url" => null,
                "prev_page_url" => null,
                'from' => (($page - 1) * $limit) + 1,
                'to' => ($page * $limit),
            ]
        ];

        return $this->serialize(
            $request,
            $results->getRows()->slice($page - 1 * $limit, $limit),
            new DistributionTotalsReportTransformer(),
            $meta
        );
    }

    /**
     *
     * @Route(path="/partner-order-totals")
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function partnerOrderTotalsReport(Request $request)
    {
        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;
        $page = $request->get('page', 1);
        $limit = $request->get('per_page', 10);

        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(Product::class);

        $params = new ParameterBag($this->getParams($request));

        if ($request->get('partner')) {
            $params->set('partner', $this->getRepository(Partner::class)->find($request->get('partner')));
        }

        if ($request->get('product')) {
            $params->set('product', $productRepo->find($request->get('product')));
        }

        /** @var PartnerOrderRepository $repo */
        $repo = $this->getRepository(PartnerOrder::class);

        /** @var PartnerOrder[] $orders */
        $orders = $repo->partnerOrderTotals(
            $sort ? $sort[0] : null,
            $sort ? $sort[1] : null,
            $params
        );

        $total = $repo->findPartnerOrderTotalsCount($params);

        $results = new PartnerOrderTotalsReport();

        foreach ($orders as $order) {
            $row = $results->getRow($order->getPartner());
            foreach ($order->getLineItems() as $lineItem) {
                $row->addProductTotal($lineItem->getProduct(), $lineItem->getQuantity());
            }
        }

        if ($request->get('download')) {
            $excelReport = new PartnerOrderTotalsExcel($results, $productRepo->findByPartnerOrderable(true));

            $writer = $excelReport->buildExcel();
            // redirect output to client browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="PartnerOrderTotals.' . date('c') . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit();
        }

        $meta = [
            'pagination' => [
                'total' => (int) $total,
                'per_page' => (int) $limit,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $limit),
                "next_page_url" => null,
                "prev_page_url" => null,
                'from' => (($page - 1) * $limit) + 1,
                'to' => ($page * $limit),
            ]
        ];

        return $this->serialize(
            $request,
            $results->getRows()->slice($page - 1 * $limit, $limit),
            new PartnerOrderTotalsReportTransformer(),
            $meta
        );
    }

    /**
     *
     * @Route(path="/partner-inventory")
     * @IsGranted({"ROLE_ADMIN"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function partnerInventoryReport(Request $request)
    {
        $sort = $request->get('sort') ? explode('|', $request->get('sort')) : null;
        $page = $request->get('page', 1);
        $limit = $request->get('per_page', 10);

        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepository(Product::class);

        $params = new ParameterBag($this->getParams($request));

        $partnerRepo = $this->getRepository(Partner::class);
        if ($params->has('partnerType')) {
            $partners = $partnerRepo->findBy(['partnerType' => $params->get('partnerType')]);
        } else {
            $partners = $partnerRepo->findAll();
        }

        /** @var InventoryTransactionRepository $transactionRepo */
        $transactionRepo = $this->getRepository(InventoryTransaction::class);
        /** @var BulkDistributionOrderRepository $distributionRepo */
        $distributionRepo = $this->getRepository(BulkDistribution::class);

        $report = new PartnerInventoryReport();

        foreach ($partners as $partner) {
            $params->set('location', $partner);
            $inventory = $transactionRepo->getStockLevels(false, $params);
            $row = $report->getRow($partner);
            foreach ($inventory as $item) {
                $row->setProductInventory($productRepo->find($item['id']), $item['balance']);
            }

            $forecastDistributions = $distributionRepo->getDistributionsForForcasting($partner);
            $row->setForecastDistributions($forecastDistributions);
        }

        if ($request->get('download')) {
            $excelReport = new PartnerInventoryExcel($report, $productRepo->findByPartnerOrderable(true));

            $writer = $excelReport->buildExcel();
            // redirect output to client browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="PartnerInventory.' . date('c') . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit();
        }

        $total = count($partners);

        $meta = [
            'pagination' => [
                'total' => (int) $total,
                'per_page' => (int) $limit,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $limit),
                "next_page_url" => null,
                "prev_page_url" => null,
                'from' => (($page - 1) * $limit) + 1,
                'to' => ($page * $limit),
            ]
        ];

        return $this->serialize(
            $request,
            $report->getRows()->slice($page - 1 * $limit, $limit),
            new PartnerInventoryReportTransformer(),
            $meta
        );
    }
}
