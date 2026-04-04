<?php

namespace App\Models;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Tenants extends Model
{   
    protected $fillable = ['business_name'];
    public function users(): HasMany 
    {
        $this->hasMany(User::class);
    }
}
