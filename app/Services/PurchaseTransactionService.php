<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\PurchaseTransaction;

class PurchaseTransactionService {

    public function getCustomerTransactionThisMonth(Customer $customer){
        return $customer->purchaseTransaction()->where('transaction_at', '>=', now()->subMonth())->get();
    }

    public function getPurchaseCountAndTotalSpent(Customer $customer)
    {   
        $purchaseTransaction = $this->getCustomerTransactionThisMonth($customer);
        $purchaseCount = $purchaseTransaction->count(); 
        $totalSpent = $purchaseTransaction->sum('total_spent');

        return ['purchaseCount' => $purchaseCount, 'totalSpent' => $totalSpent];
    }
}