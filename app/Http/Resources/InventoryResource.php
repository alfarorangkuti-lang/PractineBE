<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            'serial_number' => $this->serial_number,
            'price' => $this->price,
            'status' => $this->status,
            'supplier' => $this->supplier->name ?? null,
            'stockParent' => [
                'id' => $this->stockParent->id ?? null,
                'name' => $this->stockParent->name ?? null,
                'type' => $this->stockParent->type ?? null,
                'custom_fields' => collect($this->stockParent?->customFieldValues)
                    ->groupBy(fn($item) => $item->field->name)
                    ->map(function ($items) {
                        return $items->count() > 1
                            ? $items->pluck('value')
                            : $items->first()->value;
                    }),
            ],
        ];
    }
}
