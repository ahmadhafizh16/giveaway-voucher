<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\Customer;

class VoucherService {

    public function bindVoucherToCustomer(Customer $customer){
        $userVoucher = Voucher::where('customer_id',$customer->id)->first();

        if($userVoucher){
            return $userVoucher;
        }

        $voucher = Voucher::unbooked()->lockForUpdate()->first();
        $voucher->customer_id = $customer->id;
        $voucher->save();
        
        return $voucher;
    }
}