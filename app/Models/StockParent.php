<?php

namespace App\Models;
use App\Models\CustomFieldStockParent;
use Illuminate\Database\Eloquent\Model;

class StockParent extends Model
{
    public function customFieldValues()
    {
        return $this->hasMany(CustomFieldStockParent::class, 'stock_parent_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'stock_parent_id');
    }

    protected $table = 'stock_parent';
    protected $fillable = ['tenant_id', 'type', 'name', 'quantity', 'custom_field', 'created_at', 'updated_at'];
    protected $casts = [
        'custom_field' => 'array',
    ];
}
