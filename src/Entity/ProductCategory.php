<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Product
 *
 * @ORM\Entity(repositoryClass="App\Entity\ProductCategoryRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class ProductCategory extends ListOption
{
    /**
     * If true this product category will show on partner orders.
     *
     * @ORM\Column(type="boolean")
     */
    protected $isPartnerOrderable;

    /**
     * ProductCategory constructor.
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->isPartnerOrderable = true;
    }

    public function isPartnerOrderable(): bool
    {
        return $this->isPartnerOrderable;
    }

    public function setIsPartnerOrderable(bool $isPartnerOrderable)
    {
        $this->isPartnerOrderable = $isPartnerOrderable;
    }
}
