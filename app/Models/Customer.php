<?php

namespace App\Models;

use App\Models\PurchaseTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    public function purchaseTransaction()
    {
        return $this->hasMany(PurchaseTransaction::class,'customer_id');
    }
}
