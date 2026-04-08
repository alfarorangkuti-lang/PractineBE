<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockParentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'type' => $this->type,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'custom_fields' => $this->customFieldValues
                ->mapWithKeys(function ($item) {
                    return [
                        $item->field->name => $item->value
                    ];
                }),
        ];
    }
}
