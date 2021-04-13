<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WarehouseRepository")
 *
 */
class Warehouse extends StorageLocation
{
    public const ROLE_VIEW = "ROLE_WAREHOUSE_VIEW";
    public const ROLE_EDIT = "ROLE_WAREHOUSE_EDIT";
}
