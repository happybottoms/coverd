<?php

namespace App\Transformers;

use App\Entity\EAV\Attribute;
use App\Entity\EAV\ClientAttributeDefinition;

class ClientAttributeTransformer extends AttributeTransformer
{
    /**
     * @param Attribute $attribute
     * @return array
     */
    public function transform(Attribute $attribute): array
    {
        /** @var ClientAttributeDefinition $definition */
        $definition = $attribute->getDefinition();

        $fields = parent::transform($attribute);
        $fields['isDuplicateReference'] = $definition->isDuplicateReference();

        return $fields;
    }
}
