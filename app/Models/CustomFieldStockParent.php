<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StockParent;
use App\Models\CustomField;

class CustomFieldStockParent extends Model
{
    public function field()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    public function stock()
    {
        return $this->belongsTo(StockParent::class, 'stock_parent_id');
    }
    protected $table = 'custom_field_stock_parents';
    protected $fillable = ['tenant_id', 'custom_field_id', 'stock_parent_id', 'value'];
}
