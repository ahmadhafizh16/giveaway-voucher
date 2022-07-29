<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\Customer;

class VoucherService {

    public function bindVoucherToCustomer(Customer $customer){
        $voucher = Voucher::unbooked()->lockForUpdate()->first();
        $voucher->customer_id = $customer->id;
        $voucher->save();
        
        return $voucher;
    }

    public function customerHasVoucher(Customer $customer){
        $userVoucher = Voucher::where('customer_id',$customer->id)->first();
        
        return $userVoucher;
    }

    public function isVoucherAvailable(){
        return Voucher::where('customer_id',null)->count() > 0 ? true : false;
    }
}