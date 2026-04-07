<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomFieldStockParent;

class CustomField extends Model
{
    public function customFields()
    {
        return $this->hasMany(CustomFieldStockParent::class);
    }

    protected $fillable = ['tenant_id', 'name', 'type'];
}
