<?php

namespace App\Transformers;

use App\Entity\EAV\ClientAttributeDefinition;
use App\Entity\EAV\AttributeDefinition;

class ClientAttributeDefinitionTransformer extends AttributeDefinitionTransformer
{
    /**
     * @param ClientAttributeDefinition $attribute
     * @return array
     */
    public function transform(AttributeDefinition $attribute)
    {
        $fields = parent::transform($attribute);
        $fields['isDuplicateReference'] = $attribute->isDuplicateReference();

        return $fields;
    }
}
