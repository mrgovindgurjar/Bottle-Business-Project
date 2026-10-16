<?php

namespace App\Http\Requests;

class UpdateDeliveryRequest extends StoreDeliveryRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('deliveries.edit');
    }
}
