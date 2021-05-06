<?php

namespace App\Entity\EAV;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PartnerProfileDefinition
 *
 * @ORM\Entity(repositoryClass="App\Repository\DefinitionRepository")
 */
class PartnerProfileAttributeDefinition extends AttributeDefinition
{
    /**
     * @var string
     */
    public $defaultEntityName = PartnerProfileAttributeDefinition::class;
}
