<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenants;

class PaymentHistory extends Model
{
    protected $table = 'payment_history';
    protected $fillable = ['tenant_id', 'month_amount', 'pay_amount', 'status', 'snap_token', 'order_id'];
    
    public function Tenants(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }

}
