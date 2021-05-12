<?php

namespace App\Controller;

use App\Entity\EAV\ClientAttributeDefinition;
use App\Transformers\ClientAttributeDefinitionTransformer;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BaseAttributeController
 * @package App\Controller
 *
 * @Route(path="/api/client/attribute/definition")
 */
class ClientAttributeDefinitionController extends BaseAttributeDefinitionController
{
    protected $defaultEntityName = ClientAttributeDefinition::class;

    protected function getNewDefinition()
    {
        return new ClientAttributeDefinition();
    }

    protected function getDefaultTransformer(): ClientAttributeDefinitionTransformer
    {
        return new ClientAttributeDefinitionTransformer();
    }
}
