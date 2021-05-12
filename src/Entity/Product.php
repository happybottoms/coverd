<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Product
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
class Product extends CoreEntity
{
    public const STATUS_ACTIVE = "ACTIVE";
    public const STATUS_INACTIVE = "INACTIVE";
    public const STATUS_OUT_OF_STOCK = "OUT_OF_STOCK";

    public const ROLE_VIEW = "ROLE_PRODUCT_VIEW";
    public const ROLE_EDIT = "ROLE_PRODUCT_EDIT";

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string $sku
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Versioned
     */
    protected $sku;

    /**
     * @var string $name
     *
     * @ORM\Column(type="string", nullable=false)
     * @Gedmo\Versioned
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @var int $agencyPacksPerBag
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $agencyPacksPerBag;

    /**
     * @var int $agencyPackSize
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $agencyPackSize;

    /**
     * @var int $agencyMaxPacks
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $agencyMaxPacks;

    /**
     * @var int $hospitalPacksPerBag
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $hospitalPacksPerBag;

    /**
     * @var int $hospitalPackSize
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $hospitalPackSize;

    /**
     * @var int $hospitalMaxPacks
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $hospitalMaxPacks;

    /**
     * @var ProductCategory $productCategory
     *
     * @ORM\ManyToOne(targetEntity="ProductCategory")
     */
    protected $productCategory;

    /**
     * @var string $color
     *
     * @ORM\Column(type="string", length=7, nullable=true)
     * @Gedmo\Versioned
     */
    protected $color;

    /**
     * @var float $defaultCost
     *
     * @ORM\Column(type="float", nullable=true)
     * @Gedmo\Versioned
     */
    protected $defaultCost;

    /**
     * @var float $retailPrice
     *
     * @ORM\Column(type="float", nullable=true)
     * @Gedmo\Versioned
     */
    protected $retailPrice;

    /**
     * @var string $status
     *
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     */
    protected $status;

    /**
     * @var int $orderIndex
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $orderIndex;


    public function __construct($name, ProductCategory $category = null)
    {
        $this->setName($name);
        $this->setProductCategory($category);
        $this->setStatus(self::STATUS_ACTIVE);
        $this->setAgencyMaxPacks(1);
        $this->setHospitalMaxPacks(1);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku(string $sku = null)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getAgencyPacksPerBag()
    {
        return $this->agencyPacksPerBag;
    }

    /**
     * @param int $agencyPacksPerBag
     */
    public function setAgencyPacksPerBag($agencyPacksPerBag = null)
    {
        $this->agencyPacksPerBag = $agencyPacksPerBag;
    }

    /**
     * @return int
     */
    public function getAgencyPackSize()
    {
        return $this->agencyPackSize;
    }

    /**
     * @param int $agencyPackSize
     */
    public function setAgencyPackSize(int $agencyPackSize = null)
    {
        $this->agencyPackSize = $agencyPackSize;
    }

    /**
     * @return int
     */
    public function getAgencyMaxPacks(): int
    {
        return $this->agencyMaxPacks;
    }

    /**
     * @param int $agencyMaxPacks
     */
    public function setAgencyMaxPacks(int $agencyMaxPacks): void
    {
        $this->agencyMaxPacks = $agencyMaxPacks;
    }

    /**
     * @return int
     */
    public function getHospitalPacksPerBag()
    {
        return $this->hospitalPacksPerBag;
    }

    /**
     * @param int $hospitalPacksPerBag
     */
    public function setHospitalPacksPerBag(int $hospitalPacksPerBag = null)
    {
        $this->hospitalPacksPerBag = $hospitalPacksPerBag;
    }

    /**
     * @return int
     */
    public function getHospitalPackSize()
    {
        return $this->hospitalPackSize;
    }

    /**
     * @param int $hospitalPackSize
     */
    public function setHospitalPackSize(int $hospitalPackSize = null)
    {
        $this->hospitalPackSize = $hospitalPackSize;
    }

    /**
     * @return int
     */
    public function getHospitalMaxPacks(): int
    {
        return $this->hospitalMaxPacks;
    }

    /**
     * @param int $hospitalMaxPacks
     */
    public function setHospitalMaxPacks(int $hospitalMaxPacks): void
    {
        $this->hospitalMaxPacks = $hospitalMaxPacks;
    }

    public function getSmallestPackSize()
    {
        return $this->agencyPackSize;
    }

    /**
     * @return ProductCategory
     */
    public function getProductCategory()
    {
        return $this->productCategory;
    }

    /**
     * @param ProductCategory $productCategory
     */
    public function setProductCategory(ProductCategory $productCategory = null)
    {
        $this->productCategory = $productCategory;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color = null)
    {
        $this->color = $color;
    }

    /**
     * @return float
     */
    public function getDefaultCost()
    {
        return $this->defaultCost;
    }

    /**
     * @param float $defaultCost
     */
    public function setDefaultCost($defaultCost = null)
    {
        $this->defaultCost = $defaultCost;
    }

    /**
     * @return float
     */
    public function getRetailPrice()
    {
        return $this->retailPrice;
    }

    /**
     * @param float $retailPrice
     */
    public function setRetailPrice($retailPrice = null)
    {
        $this->retailPrice = $retailPrice;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
    }

    /**
     * @param int $orderIndex
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;
    }
}
