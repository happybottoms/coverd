<?php

namespace App\Controller;

use App\Entity\EAV\PartnerProfileAttributeDefinition;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BaseAttributeController
 * @package App\Controller
 *
 * @Route(path="/api/partner/attribute/definition")
 */
class PartnerProfileAttributeDefinitionController extends BaseAttributeDefinitionController
{
    protected $defaultEntityName = PartnerProfileAttributeDefinition::class;

    protected function getNewDefinition()
    {
        return new PartnerProfileAttributeDefinition();
    }
}
