<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\EligibleCheckRequest;
use App\Http\Requests\ValidatePhotoRequest;
use App\Jobs\ResetVoucherOwnershipJob;
use App\Services\PurchaseTransactionService;
use App\Services\VoucherService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VoucherGiftController extends Controller
{
    public function eligibleCheck(
            EligibleCheckRequest $request,
            PurchaseTransactionService $PTService,
            VoucherService $VoucherService,
    )
    {
        try{
            $customerId = $request->customer_id;

            $customer = Customer::select(['id','first_name','last_name','email'])->findOrFail($customerId);
            $customerTransaction = $PTService->getPurchaseCountAndTotalSpent($customer);
            $voucher = $VoucherService->customerHasVoucher($customer);

            if($voucher){
                return response()->json([
                    'message' => 'This customer can participate! Please upload your photo before '.$voucher->updated_at->addMinutes(10),
                    'data' => [
                        'isEligible' => true,
                        'transaction' => $customerTransaction,
                        'customer' => $customer,
                        'voucher' => $voucher
                    ]
                ], 200);
            }

            if(!$VoucherService->isVoucherAvailable()){
                return response()->json([
                    'message' => 'No voucher available !'
                ], 403);
            }

            if($customerTransaction['totalSpent'] < 300 || $customerTransaction['purchaseCount'] < 3)
            {
                return response()->json([
                    'message' => 'This customer is not eligible!',
                    'data' => [
                        'isEligible' => false,
                        'transaction' => $customerTransaction
                    ]
                ], 403);
            }

            DB::beginTransaction();
            $voucher = $VoucherService->bindVoucherToCustomer($customer);
            DB::commit();

            $delay = now()->addMinutes(1);

            ResetVoucherOwnershipJob::dispatch($voucher)->delay($delay); 
        }
        catch(ModelNotFoundException $ex){
            return response()->json([
                'message' => 'Customer not found !'
            ], 404);
        }
        catch(\Throwable $ex){
            DB::rollback();
            return response()->json([
                'message' => 'Something went wrong!'
            ], 500);
        }
        
        return response()->json([
            'message' => 'This customer can participate! Please upload your photo before '.$delay,
            'data' => [
                'isEligible' => true,
                'transaction' => $customerTransaction,
                'customer' => $customer,
                'voucher' => $voucher
            ]
        ], 200);
    }
    /**
     * @OA\Get(path="/customer-check",
     *     tags={"customer check"},
     *     summary="check if customer eligible",
     *     description="return link to claim voucher",
     *     operationId="checkCustomer",
     *     parameters={},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             additionalProperties={
     *                 "type": "integer",
     *                 "format": "int32"
     *             }
     *         )
     *     ),
     *     security={{
     *         "api_key": {}
     *     }}
     * )
     */
    public function validatePhoto(ValidatePhotoRequest $request)
    {
        
    }
}
