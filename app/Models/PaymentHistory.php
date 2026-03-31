<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PaymentHistory extends Model
{
    protected $table = 'payment_history';
    protected $fillable = ['user_id', 'month_amount', 'pay_amount', 'status', 'snap_token', 'order_id', 'expired_at'];
    
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
