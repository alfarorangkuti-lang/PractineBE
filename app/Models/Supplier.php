<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{

    public function inventory(){
        return $this->hasMany('inventory', 'supplier_id');
    }

    protected $table = 'supplier';
    protected $fillable = ['tenant_id', 'name', 'created_at' ,'updated_at'];
}
