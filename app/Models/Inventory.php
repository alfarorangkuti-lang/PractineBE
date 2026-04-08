<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $fillable = ['tenant_id', 'stock_parent_id','supplier_id','serial_number', 'price', 'status'];
    
    public function stockParent() {
        return $this->belongsTo(StockParent::class, 'stock_parent_id');
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
