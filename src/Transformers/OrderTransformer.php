<?php

namespace App\Transformers;

use App\Entity\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
{

    protected $availableIncludes = [
        'lineItems',
    ];


    public function transform(Order $order)
    {
        return [
            'id' => (int) $order->getId(),
            'sequence' => $order->getSequenceNo(),
            'status' => $order->getStatus(),
            'createdAt' => $order->getCreatedAt()->format('c'),
            'updatedAt' => $order->getUpdatedAt()->format('c'),
            'isEditable' => $order->isEditable(),
            'isDeletable' => $order->isDeletable(),
            'isComplete' => $order->isComplete(),
        ];
    }

    public function includeLineItems(Order $order)
    {
        $lineItems = $order->getLineItems();

        return $this->collection($lineItems, new LineItemTransformer());
    }
}
