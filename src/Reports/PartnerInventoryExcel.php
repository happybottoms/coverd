<?php

namespace App\Reports;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;

class PartnerInventoryExcel
{

    /**
     * @var PartnerInventoryReport
     */
    protected $reportData;

    /**
     * @var Product[]|ArrayCollection
     */
    protected $products;

    /**
     * PartnerTotalsExcel constructor.
     * @param PartnerInventoryReport $reportData
     * @param Product[]|ArrayCollection $products
     */
    public function __construct(PartnerInventoryReport $reportData, $products)
    {
        $this->reportData = $reportData;
        $this->products = $products;
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Writer\IWriter
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function buildExcel(): IWriter
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Headers
        $sheet->fromArray($this->buildHeaders());

        $dataArr = [];
        foreach ($this->reportData->getRows() as $rowData) {
            $rowArr = [];
            $rowArr[] = $rowData->getPartner()->getId();
            $rowArr[] = $rowData->getPartner()->getTitle();
            $rowArr[] = $rowData->getPartner()->getPartnerType();

            foreach ($this->products as $product) {
                $rowArr[] = $rowData->getProductTotal($product);
            }

            $rowArr[] = $rowData->getTotal();

            foreach ($this->products as $product) {
                $rowArr[] = $rowData->getProductForecast($product);
            }

            $dataArr[] = $rowArr;
        }
        $sheet->fromArray($dataArr, null, 'A2');

        $sheet->freezePaneByColumnAndRow(3, 2);

        return IOFactory::createWriter($spreadsheet, 'Xlsx');
    }

    private function buildHeaders()
    {
        $headers = [
            'Partner Id',
            'Partner',
            'Partner Type',
        ];

        foreach ($this->products as $product) {
            $headers[] = $product->getName();
        }

        $headers[] = 'Total Inventory';

        foreach ($this->products as $product) {
            $headers[] = "Months left: " . $product->getName();
        }

        return $headers;
    }
}
