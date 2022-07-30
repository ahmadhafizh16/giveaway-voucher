<?php

namespace App\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory;

    protected $hidden = ['id'];

    protected $casts = [
        'is_claimed' => 'boolean'
    ];

    public function scopeUnbooked($query)
    {
        return $this->where('customer_id', null)->where('is_claimed',0);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
}
